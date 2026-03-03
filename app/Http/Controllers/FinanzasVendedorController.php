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

        $query = Pasivo::where('sucursal_id', Auth::user()->branch_id)
            ->whereHas('tipo', function ($q) use ($nombresTipos, $tipoFiltro, $tipoMap) {
                if ($tipoFiltro && isset($tipoMap[$tipoFiltro])) {
                    $q->where('nombre', $tipoMap[$tipoFiltro]);
                } else {
                    $q->whereIn('nombre', $nombresTipos);
                }
            });

        // "Una vez saldado debería desaparecer el registro" - Para Adelantos Personal y Clientes
        $query->where(function ($q) {
            $q->whereDoesntHave('tipo', function ($t) {
                $t->whereIn('nombre', ['Adelantos personal', 'Adelanto clientes', 'Adelanto de clientes']);
            })->orWhere('estado', '!=', 'pagado');
        });

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
        } else {
            $operaciones = $query->with(['tipo', 'pagos'])->orderBy('fecha_registro', 'desc')->paginate(20)->appends($request->query());
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

    public function store(Request $request)
    {
        $request->validate([
            'tipo_operacion' => 'required|in:compras_credito,adelanto_clientes,adelanto_personal',
            'monto' => 'required|numeric|min:0.01',
            'empresa_persona' => 'required|string|max:255',
            'nombre' => 'required|string|max:255',
            'fecha_registro' => 'required|date',
            'documento' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string'
        ]);

        $tipoOperacion = $request->input('tipo_operacion');
        $monto = $request->input('monto');
        $empresaPersona = $request->input('empresa_persona');
        $nombre = $request->input('nombre');
        $fecha = $request->input('fecha_registro');
        $documento = $request->input('documento');
        $observaciones = $request->input('observaciones');

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

            // Buscar sin scope de empresa (TipoPasivo es compartido)
            $tipoPasivo = TipoPasivo::where('nombre', $nombreTipo)->first();
            if (!$tipoPasivo) {
                $tipoPasivo = TipoPasivo::create([
                    'nombre'     => $nombreTipo,
                    'descripcion' => 'Registrado por vendedor'
                ]);
            }

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
                'observaciones' => $observaciones
            ]);

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

            // Validar que haya caja para operaciones que mueven efectivo
            if (in_array($tipoOperacion, ['adelanto_clientes', 'adelanto_personal']) && !$cajaAbierta) {
                throw new \Exception('No se puede registrar esta operación porque no tienes una caja abierta. Por favor, abre una caja antes de continuar.');
            }

            if ($cajaAbierta) {
                // Adelanto clientes: Entra dinero (Ingreso)
                if ($tipoOperacion === 'adelanto_clientes') {
                    $cajaAbierta->ingresos = ($cajaAbierta->ingresos ?? 0) + $monto;
                    $cajaAbierta->save();

                    OperacionCaja::create([
                        'cierre_caja_id' => $cajaAbierta->id,
                        'user_id' => Auth::id(),
                        'tipo' => 'ingreso',
                        'partida' => 'Adelanto clientes',
                        'concepto' => 'Adelanto de cliente: ' . $nombre,
                        'importe' => $monto,
                    ]);
                }
                // Adelantos personal: Sale dinero (Gasto)
                elseif ($tipoOperacion === 'adelanto_personal') {
                    $cajaAbierta->egresos = ($cajaAbierta->egresos ?? 0) + $monto;
                    $cajaAbierta->save();

                    OperacionCaja::create([
                        'cierre_caja_id' => $cajaAbierta->id,
                        'user_id' => Auth::id(),
                        'tipo' => 'gasto',
                        'partida' => 'Adelantos personal',
                        'concepto' => 'Adelanto a personal: ' . $nombre,
                        'importe' => $monto,
                    ]);
                }
            }

            DB::commit();

            if (in_array($tipoOperacion, ['adelanto_personal', 'adelanto_clientes', 'compras_credito'])) {
                return redirect()->route('finanzas_vendedor.index', ['tipo' => $tipoOperacion])
                    ->with('success', 'Operación registrada correctamente.')
                    ->with('imprimir_pasivo_id', $pasivo->id);
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
        $operacion = Pasivo::with('tipo')->findOrFail($id);

        // Determinar el tipo_operacion para el select
        $tipoOperacion = '';
        if ($operacion->tipo->nombre === 'Compras a crédito') {
            $tipoOperacion = 'compras_credito';
        } elseif ($operacion->tipo->nombre === 'Adelanto clientes') {
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
            'observaciones' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $operacion = Pasivo::findOrFail($id);

            $nombreTipo = '';
            if ($request->tipo_operacion === 'compras_credito') {
                $nombreTipo = 'Compras a crédito';
            } elseif ($request->tipo_operacion === 'adelanto_clientes') {
                $nombreTipo = 'Adelanto clientes';
            } elseif ($request->tipo_operacion === 'adelanto_personal') {
                $nombreTipo = 'Adelantos personal';
            }

            $tipoPasivo = TipoPasivo::where('nombre', $nombreTipo)->first();

            $operacion->update([
                'tipo_pasivo_id' => $tipoPasivo->id,
                'nombre' => $request->nombre,
                'empresa_persona' => $request->empresa_persona,
                'monto' => $request->monto,
                'fecha_registro' => $request->fecha_registro,
                'documento' => $request->documento,
                'observaciones' => $request->observaciones
            ]);

            DB::commit();

            return redirect()->route('finanzas_vendedor.index')
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

            // Compras a crédito no afectan a caja por solicitud

            DB::commit();
            return redirect()->route('finanzas_vendedor.index', ['agrupar' => 1])
                ->with('success', 'Pago acumulado de S/ ' . number_format($montoInicial, 2) . ' aplicado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al procesar el pago: ' . $e->getMessage());
        }
    }
}
