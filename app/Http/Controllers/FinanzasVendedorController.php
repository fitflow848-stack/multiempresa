<?php

namespace App\Http\Controllers;

use App\Models\ActivoCorriente;
use App\Models\Pasivo;
use App\Models\TipoActivoCorriente;
use App\Models\TipoPasivo;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Models\CierreCaja;
use App\Models\OperacionCaja;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FinanzasExport;

class FinanzasVendedorController extends Controller
{
    public function index(Request $request)
    {
        $tipoMap = [
            'compras_credito'   => 'Compras a crédito',
            'adelanto_clientes' => 'Adelanto clientes',
            'adelanto_personal' => 'Adelantos personal',
        ];

        $tipoFiltro = $request->get('tipo');
        $fechaDesde = $request->get('fecha_desde');
        $fechaHasta = $request->get('fecha_hasta');
        $search = $request->get('search');
        $agrupar = $request->get('agrupar', 0);

        $nombresTipos = ['Compras a crédito', 'Adelanto clientes', 'Adelantos personal'];

        // ─── Pasivos (Compras crédito + Adelanto clientes + Adelantos personal desde finanzas) ───
        $query = Pasivo::where('sucursal_id', Auth::user()->branch_id)
            ->whereHas('tipo', function ($q) use ($nombresTipos, $tipoFiltro, $tipoMap) {
                if ($tipoFiltro && isset($tipoMap[$tipoFiltro])) {
                    $q->where('nombre', $tipoMap[$tipoFiltro]);
                } else {
                    $q->whereIn('nombre', $nombresTipos);
                }
            });

        // Quitamos el filtro que ocultaba los saldados para que el usuario pueda ver el historial
        // y realizar acciones como imprimir ticket o editar/eliminar si fuera necesario.

        // Filtros adicionales
        if ($fechaDesde) {
            $query->whereDate('fecha_registro', '>=', $fechaDesde);
        }
        if ($fechaHasta) {
            $query->whereDate('fecha_registro', '<=', $fechaHasta);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('empresa_persona', 'like', "%$search%")
                    ->orWhere('nombre', 'like', "%$search%")
                    ->orWhere('documento', 'like', "%$search%");
            });
        }

        // ─── ActivoCorriente de Adelantos a Personal registrados desde Caja ───
        // Solo se muestran si el tab es "adelanto_personal" o "Todos"
        $activosPersonalItems = collect();
        if (!$tipoFiltro || $tipoFiltro === 'adelanto_personal') {
            $activosQuery = ActivoCorriente::where('sucursal_id', Auth::user()->branch_id)
                ->whereHas('tipo', function ($q) {
                    $q->where('nombre', 'Adelantos a Personal');
                })
                ->orderBy('fecha_registro', 'desc')
                ->orderBy('id', 'desc');
                // ->where('is_settled', false); // Permitir ver los saldados

            if ($fechaDesde) {
                $activosQuery->whereDate('fecha_registro', '>=', $fechaDesde);
            }
            if ($fechaHasta) {
                $activosQuery->whereDate('fecha_registro', '<=', $fechaHasta);
            }
            if ($search) {
                $activosQuery->where(function ($q) use ($search) {
                    $q->where('nombre', 'like', "%$search%")
                        ->orWhere('observaciones', 'like', "%$search%");
                });
            }

            // Convertir a formato compatible con la vista (similar a Pasivo)
            $activosPersonalItems = $activosQuery->with('tipo')->get()->map(function ($activo) {
                $monto_pagado = $activo->is_settled ? $activo->monto : 0;
                $saldo = $activo->is_settled ? 0 : $activo->monto;
                return (object) [
                    'id'              => 'activo_' . $activo->id,
                    '_activo_id'      => $activo->id,
                    '_es_activo'      => true,
                    'fecha_registro'  => $activo->fecha_registro,
                    'tipo'            => (object) ['nombre' => 'Adelantos personal'],
                    'empresa_persona' => $activo->nombre,
                    'nombre'          => $activo->observaciones ?? 'Adelanto desde caja',
                    'documento'       => $activo->documento,
                    'monto'           => $activo->monto,
                    'monto_pagado'    => $monto_pagado,
                    'saldo'           => $saldo,
                    'estado'          => $activo->is_settled ? 'pagado' : 'aprobado',
                    'pagos'           => collect(),
                    'created_at'      => $activo->created_at,
                ];
            });
        }

        if ($agrupar) {
            $operaciones = $query->select(
                'empresa_persona',
                DB::raw('SUM(monto) as total_monto'),
                DB::raw('SUM(monto_pagado) as total_pagado'),
                DB::raw('COUNT(*) as cantidad_operaciones')
            )
                ->groupBy('empresa_persona')
                ->orderBy('total_monto', 'desc')
                ->get()
                ->map(function ($item) {
                    $item->saldo = $item->total_monto - $item->total_pagado;
                    return $item;
                });

            // Fusionar activos de personal en la vista agrupada
            foreach ($activosPersonalItems as $item) {
                $existing = $operaciones->firstWhere('empresa_persona', $item->empresa_persona);
                if ($existing) {
                    $existing->total_monto += $item->monto;
                    $existing->saldo += $item->saldo;
                    $existing->cantidad_operaciones++;
                } else {
                    $operaciones->push((object) [
                        'empresa_persona'      => $item->empresa_persona,
                        'total_monto'          => $item->monto,
                        'total_pagado'         => 0,
                        'saldo'                => $item->saldo,
                        'cantidad_operaciones' => 1,
                    ]);
                }
            }
        } else {
            // Vista detallada: combinar pasivos + activos de personal
            $pasivosCollection = $query->with(['tipo', 'pagos'])
                ->orderBy('fecha_registro', 'desc')
                ->orderBy('id', 'desc')
                ->get();

            // Unir las dos colecciones y ordenar por fecha descendente y ID descendente
            $merged = $pasivosCollection->concat($activosPersonalItems)
                ->sort(function ($a, $b) {
                    // Primero por fecha
                    $fechaA = $a->fecha_registro;
                    $fechaB = $b->fecha_registro;

                    if ($fechaA->ne($fechaB)) {
                        return $fechaB->gt($fechaA) ? 1 : -1;
                    }

                    // Si la fecha es igual, desempatar con ID (extrayendo el número si es activo_ID)
                    $idA = (int) str_replace('activo_', '', (string) $a->id);
                    $idB = (int) str_replace('activo_', '', (string) $b->id);

                    return $idB <=> $idA;
                })
                ->values();

            // Paginar manualmente
            $perPage = 20;
            $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage();
            $currentItems = $merged->slice(($currentPage - 1) * $perPage, $perPage)->values();
            $operaciones = new \Illuminate\Pagination\LengthAwarePaginator(
                $currentItems,
                $merged->count(),
                $perPage,
                $currentPage,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        }

        $tipoActivo = $tipoFiltro;
        $titulos = [
            'adelanto_personal' => 'Adelantos a Personal',
            'compras_credito'   => 'Compras a Crédito',
            'adelanto_clientes' => 'Adelanto de Clientes',
        ];
        $tituloSeccion = $titulos[$tipoFiltro] ?? 'Todas las Operaciones';

        return view('finanzas_vendedor.index', compact('operaciones', 'tipoActivo', 'tituloSeccion', 'agrupar', 'fechaDesde', 'fechaHasta', 'search'));
    }

    public function export(Request $request)
    {
        $tipoMap = [
            'compras_credito'   => 'Compras a crédito',
            'adelanto_clientes' => 'Adelanto clientes',
            'adelanto_personal' => 'Adelantos personal',
        ];

        $tipoFiltro = $request->get('tipo');
        $fechaDesde = $request->get('fecha_desde');
        $fechaHasta = $request->get('fecha_hasta');
        $search = $request->get('search');

        $nombresTipos = ['Compras a crédito', 'Adelanto clientes', 'Adelantos personal'];

        // ─── Pasivos ───
        $query = Pasivo::where('sucursal_id', Auth::user()->branch_id)
            ->whereHas('tipo', function ($q) use ($nombresTipos, $tipoFiltro, $tipoMap) {
                if ($tipoFiltro && isset($tipoMap[$tipoFiltro])) {
                    $q->where('nombre', $tipoMap[$tipoFiltro]);
                } else {
                    $q->whereIn('nombre', $nombresTipos);
                }
            });

        if ($fechaDesde) $query->whereDate('fecha_registro', '>=', $fechaDesde);
        if ($fechaHasta) $query->whereDate('fecha_registro', '<=', $fechaHasta);
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('empresa_persona', 'like', "%$search%")
                    ->orWhere('nombre', 'like', "%$search%")
                    ->orWhere('documento', 'like', "%$search%");
            });
        }

        // ─── ActivoCorriente (Adelantos a Personal) ───
        $activosPersonalItems = collect();
        if (!$tipoFiltro || $tipoFiltro === 'adelanto_personal') {
            $activosQuery = ActivoCorriente::where('sucursal_id', Auth::user()->branch_id)
                ->whereHas('tipo', function ($q) {
                    $q->where('nombre', 'Adelantos a Personal');
                });

            if ($fechaDesde) $activosQuery->whereDate('fecha_registro', '>=', $fechaDesde);
            if ($fechaHasta) $activosQuery->whereDate('fecha_registro', '<=', $fechaHasta);
            if ($search) {
                $activosQuery->where(function ($q) use ($search) {
                    $q->where('nombre', 'like', "%$search%")
                        ->orWhere('observaciones', 'like', "%$search%");
                });
            }

            $activosPersonalItems = $activosQuery->with('tipo')->get()->map(function ($activo) {
                $monto_pagado = $activo->is_settled ? $activo->monto : 0;
                $saldo = $activo->is_settled ? 0 : $activo->monto;
                return (object) [
                    'id'              => 'activo_' . $activo->id,
                    '_activo_id'      => $activo->id,
                    '_es_activo'      => true,
                    'fecha_registro'  => $activo->fecha_registro,
                    'tipo'            => (object) ['nombre' => 'Adelantos personal'],
                    'empresa_persona' => $activo->nombre,
                    'nombre'          => $activo->observaciones ?? 'Adelanto desde caja',
                    'documento'       => $activo->documento,
                    'monto'           => $activo->monto,
                    'monto_pagado'    => $monto_pagado,
                    'saldo'           => $saldo,
                    'estado'          => $activo->is_settled ? 'pagado' : 'aprobado',
                    'metodo_pago'     => $activo->metodo_pago,
                    'created_at'      => $activo->created_at,
                ];
            });
        }

        $pasivosCollection = $query->with(['tipo'])->get();
        $merged = $pasivosCollection->concat($activosPersonalItems)
            ->sortByDesc('fecha_registro')
            ->values();

        $titulos = [
            'adelanto_personal' => 'Adelantos a Personal',
            'compras_credito'   => 'Compras a Crédito',
            'adelanto_clientes' => 'Adelanto de Clientes',
        ];
        $tituloSeccion = $titulos[$tipoFiltro] ?? 'Todas las Operaciones';

        return Excel::download(
            new FinanzasExport($merged, $tituloSeccion),
            'Export_Finanzas_' . date('Ymd_His') . '.xlsx'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo_operacion' => 'required|in:compras_credito,adelanto_clientes,adelanto_personal',
            'monto' => 'required|numeric|min:0.01',
            'empresa_persona' => 'required|string|max:255',
            'nombre' => 'required|string|max:255',
            'fecha_registro' => 'required|date',
            'documento' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string',
            'metodo_pago' => 'nullable|string|max:255'
        ]);

        $tipoOperacion = $request->input('tipo_operacion');
        $monto = $request->input('monto');
        $empresaPersona = $request->input('empresa_persona');
        $nombre = $request->input('nombre');
        $fecha = $request->input('fecha_registro');
        $documento = $request->input('documento');
        $observaciones = $request->input('observaciones');
        $metodoPago = $request->input('metodo_pago');

        try {
            DB::beginTransaction();

            $nombreTipo = '';
            if ($tipoOperacion === 'compras_credito') {
                $nombreTipo = 'Compras a crédito';
            } elseif ($tipoOperacion === 'adelanto_clientes') {
                $nombreTipo = 'Adelanto clientes';
            } elseif ($tipoOperacion === 'adelanto_personal') {
                $nombreTipo = 'Adelantos personal';
            }

            // Obtener o crear el TipoPasivo para la empresa actual (company-scoped)
            $tipoPasivo = TipoPasivo::withoutGlobalScopes()
                ->firstOrCreate(
                    ['nombre' => $nombreTipo, 'company_id' => Auth::user()->company_id],
                    ['descripcion' => 'Tipo de operación: ' . $nombreTipo]
                );

            $pasivo = null; // Se creará después de la lógica de caja

            // Lógica de Caja
            $selectedCajaId = session('selected_caja_id');
            $cajaAbierta = null;

            if ($selectedCajaId) {
                $cajaAbierta = CierreCaja::where('user_id', Auth::id())
                    ->where('caja_id', $selectedCajaId)
                    ->whereNull('fecha_cierre')
                    ->first();
            } else {
                // Si no hay seleccionada, buscar la única abierta por el usuario
                $cajaAbierta = CierreCaja::where('user_id', Auth::id())
                    ->whereNull('fecha_cierre')
                    ->first();
            }

            // Validar que haya caja para operaciones que mueven efectivo (ahora todas)
            if (!$cajaAbierta) {
                throw new \Exception('No se puede registrar esta operación porque no tienes una caja abierta. Por favor, abre una caja antes de continuar.');
            }

            $operacionCajaId = null;
            if ($cajaAbierta) {
                // Todas estas operaciones ahora son sustracciones de efectivo por requerimiento
                $cajaAbierta->sustracciones = ($cajaAbierta->sustracciones ?? 0) + $monto;
                $cajaAbierta->save();

                $partida = '';
                if ($tipoOperacion === 'compras_credito') $partida = 'Compras a crédito';
                elseif ($tipoOperacion === 'adelanto_clientes') $partida = 'Adelanto clientes';
                elseif ($tipoOperacion === 'adelanto_personal') $partida = 'Adelantos personal';

                $opCaja = OperacionCaja::create([
                    'company_id' => Auth::user()->company_id,
                    'sucursal_id' => Auth::user()->branch_id,
                    'cierre_caja_id' => $cajaAbierta->id,
                    'user_id' => Auth::id(),
                    'tipo' => 'sustraccion',
                    'partida' => $partida,
                    'concepto' => $partida . ': ' . $nombre,
                    'importe' => $monto,
                    'metodo_pago' => 'Efectivo',
                ]);
                $operacionCajaId = $opCaja->id;
            }

            // Crear el registro financiero principal (Pasivo o ActivoCorriente)
            if ($tipoOperacion === 'adelanto_personal') {
                $tipoActivoCorr = TipoActivoCorriente::withoutGlobalScopes()
                    ->firstOrCreate(
                        ['nombre' => 'Adelantos a Personal', 'company_id' => Auth::user()->company_id],
                        ['descripcion' => 'Adelantos de sueldo entregados al personal']
                    );

                $pasivo = ActivoCorriente::create([
                    'company_id' => Auth::user()->company_id,
                    'sucursal_id' => Auth::user()->branch_id,
                    'tipo_activo_corriente_id' => $tipoActivoCorr->id,
                    'nombre' => $empresaPersona,
                    'monto' => $monto,
                    'fecha_registro' => $fecha,
                    'documento' => $documento,
                    'observaciones' => $nombre,
                    'user_id' => Auth::id(),
                    'cierre_caja_id' => $cajaAbierta ? $cajaAbierta->id : null,
                    'id_operacion_caja' => $operacionCajaId,
                    'is_settled' => false,
                    'tipo_adelanto' => 'personal',
                    'metodo_pago' => 'Efectivo'
                ]);
            } else {
                $pasivo = Pasivo::create([
                    'company_id' => Auth::user()->company_id,
                    'sucursal_id' => Auth::user()->branch_id,
                    'tipo_pasivo_id' => $tipoPasivo->id,
                    'nombre' => $nombre,
                    'empresa_persona' => $empresaPersona,
                    'monto' => $monto,
                    'monto_pagado' => 0,
                    'estado' => 'aprobado',
                    'fecha_registro' => $fecha,
                    'documento' => $documento,
                    'observaciones' => $observaciones,
                    'cierre_caja_id' => $cajaAbierta ? $cajaAbierta->id : null,
                    'id_operacion_caja' => $operacionCajaId,
                    'metodo_pago' => 'Efectivo'
                ]);
            }

            DB::commit();

            if (in_array($tipoOperacion, ['adelanto_personal', 'adelanto_clientes', 'compras_credito'])) {
                $sessionKey = ($tipoOperacion === 'adelanto_personal') ? 'imprimir_adelanto_id' : 'imprimir_pasivo_id';
                return redirect()->route('finanzas_vendedor.index', ['tipo' => $tipoOperacion])
                    ->with('success', 'Operación registrada correctamente.')
                    ->with($sessionKey, $pasivo->id);
            }

            return redirect()->route('finanzas_vendedor.index')
                ->with('success', 'Operación registrada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al registrar la operación: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        if (str_starts_with($id, 'activo_')) {
            $realId = str_replace('activo_', '', $id);
            $activo = ActivoCorriente::with('tipo')->findOrFail($realId);

            return response()->json([
                'success' => true,
                'operacion' => [
                    'id' => $id,
                    'empresa_persona' => $activo->nombre,
                    'nombre' => $activo->observaciones ?? 'Adelanto desde caja',
                    'monto' => $activo->monto,
                    'fecha_registro' => $activo->fecha_registro->format('Y-m-d'),
                    'documento' => $activo->documento,
                    'observaciones' => $activo->observaciones,
                ],
                'tipo_operacion' => 'adelanto_personal'
            ]);
        }

        $operacion = Pasivo::withoutGlobalScopes()->with('tipo')->findOrFail($id);

        // Determinar el tipo_operacion para el select
        $tipoOperacion = '';
        if ($operacion->tipo->nombre === 'Compras a crédito') {
            $tipoOperacion = 'compras_credito';
        } elseif ($operacion->tipo->nombre === 'Adelanto clientes' || $operacion->tipo->nombre === 'Adelanto de Clientes') {
            $tipoOperacion = 'adelanto_clientes';
        } elseif ($operacion->tipo->nombre === 'Adelantos personal') {
            $tipoOperacion = 'adelanto_personal';
        }

        return response()->json([
            'success' => true,
            'operacion' => $operacion,
            'tipo_operacion' => $tipoOperacion
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tipo_operacion' => 'required|in:compras_credito,adelanto_clientes,adelanto_personal',
            'monto' => 'required|numeric|min:0.01',
            'empresa_persona' => 'required|string|max:255',
            'nombre' => 'required|string|max:255',
            'fecha_registro' => 'required|date',
            'documento' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string',
            'metodo_pago' => 'nullable|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            if (str_starts_with($id, 'activo_')) {
                $realId = str_replace('activo_', '', $id);
                $activo = \App\Models\ActivoCorriente::findOrFail($realId);
                $activo->update([
                    'nombre' => $request->empresa_persona,
                    'monto' => $request->monto,
                    'fecha_registro' => $request->fecha_registro,
                    'documento' => $request->documento,
                    'observaciones' => $request->nombre,
                    'metodo_pago' => 'Efectivo'
                ]);
            } else {
                $operacion = Pasivo::withoutGlobalScopes()->findOrFail($id);

                $nombreTipo = '';
                if ($request->tipo_operacion === 'compras_credito') {
                    $nombreTipo = 'Compras a crédito';
                } elseif ($request->tipo_operacion === 'adelanto_clientes') {
                    $nombreTipo = 'Adelanto clientes';
                } elseif ($request->tipo_operacion === 'adelanto_personal') {
                    $nombreTipo = 'Adelantos personal';
                }

                $tipoPasivo = TipoPasivo::withoutGlobalScopes()
                    ->firstOrCreate(
                        ['nombre' => $nombreTipo, 'company_id' => Auth::user()->company_id],
                        ['descripcion' => 'Tipo de operación: ' . $nombreTipo]
                    );

                $operacion->update([
                    'tipo_pasivo_id' => $tipoPasivo->id,
                    'nombre' => $request->nombre,
                    'empresa_persona' => $request->empresa_persona,
                    'monto' => $request->monto,
                    'fecha_registro' => $request->fecha_registro,
                    'documento' => $request->documento,
                    'observaciones' => $request->observaciones,
                    'metodo_pago' => 'Efectivo'
                ]);
            }

            DB::commit();

            return redirect()->route('finanzas_vendedor.index', ['tipo' => $request->tipo_operacion])
                ->with('success', 'Operación actualizada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar la operación: ' . $e->getMessage());
        }
    }

    public function registrarPagoAcumulado(Request $request)
    {
        $request->validate([
            'empresa_persona' => 'required|string',
            'monto' => 'required|numeric|min:0.01',
            'metodo_pago' => 'required|string',
            'fecha_pago' => 'required|date',
            'observaciones' => 'nullable|string'
        ]);

        $montoRestante = floatval($request->monto);
        $montoInicial = $montoRestante;
        $empresaPersona = $request->empresa_persona;

        // Buscar pasivos con saldo de esta persona/empresa (solo Compras a Crédito)
        $pasivos = Pasivo::where('empresa_persona', $empresaPersona)
            ->whereHas('tipo', function ($q) {
                $q->where('nombre', 'Compras a crédito');
            })
            ->whereRaw('monto > monto_pagado')
            ->orderBy('fecha_registro', 'asc')
            ->get();

        if ($pasivos->isEmpty()) {
            return back()->with('error', 'No hay deudas pendientes para: ' . $empresaPersona);
        }

        DB::beginTransaction();
        try {
            $selectedCajaId = session('selected_caja_id');
            $cajaAbierta = null;

            if ($selectedCajaId) {
                $cajaAbierta = CierreCaja::where('user_id', Auth::id())
                    ->where('caja_id', $selectedCajaId)
                    ->whereNull('fecha_cierre')
                    ->first();
            } else {
                $cajaAbierta = CierreCaja::where('user_id', Auth::id())
                    ->whereNull('fecha_cierre')
                    ->first();
            }

            if (!$cajaAbierta) {
                throw new \Exception('No se puede registrar el pago porque no tienes una caja abierta.');
            }

            foreach ($pasivos as $pasivo) {
                if ($montoRestante <= 0) break;

                $pagoMonto = min($montoRestante, $pasivo->saldo);

                \App\Models\PasivoPago::create([
                    'pasivo_id' => $pasivo->id,
                    'user_id' => Auth::id(),
                    'monto' => $pagoMonto,
                    'fecha_pago' => $request->fecha_pago,
                    'metodo_pago' => $request->metodo_pago,
                    'observaciones' => $request->observaciones ? ('Pago acumulado: ' . $request->observaciones) : 'Pago acumulado'
                ]);

                $pasivo->monto_pagado += $pagoMonto;
                if ($pasivo->monto_pagado >= $pasivo->monto) {
                    $pasivo->estado = 'pagado';
                } else {
                    $pasivo->estado = 'parcial';
                }
                $pasivo->save();

                $montoRestante -= $pagoMonto;
            }

            // Afectar caja como sustracción (Efectivo) por el total pagado
            $cajaAbierta->sustracciones = ($cajaAbierta->sustracciones ?? 0) + $montoInicial;
            $cajaAbierta->save();

            OperacionCaja::create([
                'company_id' => Auth::user()->company_id,
                'sucursal_id' => Auth::user()->branch_id,
                'cierre_caja_id' => $cajaAbierta->id,
                'user_id' => Auth::id(),
                'tipo' => 'sustraccion',
                'partida' => 'Pago Acumulado',
                'concepto' => 'Pago acumulado a: ' . $empresaPersona,
                'importe' => $montoInicial,
                'metodo_pago' => 'Efectivo',
            ]);

            DB::commit();
            return redirect()->route('finanzas_vendedor.index', ['agrupar' => 1])
                ->with('success', 'Pago acumulado de S/ ' . number_format($montoInicial, 2) . ' aplicado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al procesar el pago: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            if (str_starts_with($id, 'activo_')) {
                $realId = str_replace('activo_', '', $id);
                $activo = \App\Models\ActivoCorriente::findOrFail($realId);
                
                // Si tiene operación en caja asociada, tal vez deberíamos revertirla?
                // El usuario no lo pidió explícitamente, pero es buena práctica.
                // Por ahora solo eliminamos el registro financiero.
                $activo->delete();
            } else {
                $operacion = Pasivo::withoutGlobalScopes()->findOrFail($id);
                $operacion->delete();
            }

            DB::commit();
            return back()->with('success', 'Operación eliminada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }
}
