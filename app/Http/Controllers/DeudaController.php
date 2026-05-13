<?php

namespace App\Http\Controllers;

use App\Models\Deuda;
use App\Models\Cliente;
use App\Models\DeudaPago;
use App\Models\Venta;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class DeudaController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Cargar sucursales para el filtro
        $sucursales = \App\Models\Sucursal::where('company_id', $user->company_id)->get();

        // Si el usuario es super_admin o admin_empresa, permitimos ver de otras sucursales
        // O si el usuario específicamente quiere ver "todas" (consolidado)
        // Para igualar al reporte, por defecto si no hay filtro mostramos lo de la empresa
        
        $query = Cliente::query();
        
        // Filtro opcional por sucursal (si se desea mantener el comportamiento por defecto de la sesión)
        // Pero el reporte muestra todo por defecto si no se filtra local.
        if ($request->has('sucursal_id') && !empty($request->sucursal_id)) {
            $query->where('sucursal_id', $request->sucursal_id);
        }

        $query->whereHas('deudas', function ($q) use ($request) {
                if ($request->has('estado') && !empty($request->estado)) {
                    $q->where('estado', $request->estado);
                } elseif (!$request->has('mostrar_todas')) {
                    $q->where('estado', '!=', Deuda::ESTADO_PAGADA);
                }
                
                if ($request->has('sucursal_id') && !empty($request->sucursal_id)) {
                    $q->where('sucursal_id', $request->sucursal_id);
                }

                if ($request->fecha_desde)
                    $q->where('fecha_venta', '>=', $request->fecha_desde);
                if ($request->fecha_hasta)
                    $q->where('fecha_venta', '<=', $request->fecha_hasta);
            })
            ->withCount([
                'deudas' => function ($q) use ($request) {
                    if ($request->has('estado') && !empty($request->estado)) {
                        $q->where('estado', $request->estado);
                    } elseif (!$request->has('mostrar_todas')) {
                        $q->where('estado', '!=', Deuda::ESTADO_PAGADA);
                    }
                    if ($request->has('sucursal_id') && !empty($request->sucursal_id)) {
                        $q->where('sucursal_id', $request->sucursal_id);
                    }
                    if ($request->fecha_desde)
                        $q->where('fecha_venta', '>=', $request->fecha_desde);
                    if ($request->fecha_hasta)
                        $q->where('fecha_venta', '<=', $request->fecha_hasta);
                }
            ])
            ->withSum([
                'deudas' => function ($q) use ($request) {
                    if ($request->has('estado') && !empty($request->estado)) {
                        $q->where('estado', $request->estado);
                    } elseif (!$request->has('mostrar_todas')) {
                        $q->where('estado', '!=', Deuda::ESTADO_PAGADA);
                    }
                    if ($request->has('sucursal_id') && !empty($request->sucursal_id)) {
                        $q->where('sucursal_id', $request->sucursal_id);
                    }
                    if ($request->fecha_desde)
                        $q->where('fecha_venta', '>=', $request->fecha_desde);
                    if ($request->fecha_hasta)
                        $q->where('fecha_venta', '<=', $request->fecha_hasta);
                }
            ], 'monto_deuda')
            ->with([
                'deudas' => function ($q) use ($request) {
                    if ($request->has('estado') && !empty($request->estado)) {
                        $q->where('estado', $request->estado);
                    } elseif (!$request->has('mostrar_todas')) {
                        $q->where('estado', '!=', Deuda::ESTADO_PAGADA);
                    }
                    if ($request->has('sucursal_id') && !empty($request->sucursal_id)) {
                        $q->where('sucursal_id', $request->sucursal_id);
                    }
                    if ($request->fecha_desde)
                        $q->where('fecha_venta', '>=', $request->fecha_desde);
                    if ($request->fecha_hasta)
                        $q->where('fecha_venta', '<=', $request->fecha_hasta);
                    $q->orderBy('fecha_venta', 'desc')->with('pagos');
                }
            ]);

        // Busqueda por nombre cliente
        if ($request->has('search') && !empty($request->search)) {
            $query->where(function ($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->search . '%')
                    ->orWhere('numero_documento', 'like', '%' . $request->search . '%');
            });
        }

        $clientes = $query->orderByDesc('deudas_sum_monto_deuda')
            ->paginate(15);

        // Estadísticas rápidas (Empresa-wide por defecto si no se filtra)
        $statsQuery = Deuda::query();
        if ($request->has('sucursal_id') && !empty($request->sucursal_id)) {
            $statsQuery->where('sucursal_id', $request->sucursal_id);
        }

        $estadisticas = [
            'total_pendiente' => (clone $statsQuery)->pendientes()->sum('monto_deuda'),
            'cantidad_pendiente' => (clone $statsQuery)->pendientes()->count(),
            'vencidas' => (clone $statsQuery)->vencidas()->count(),
        ];

        return view('deudas.index', compact('clientes', 'estadisticas', 'sucursales'));
    }

    public function deudasPorCliente(Request $request, $id)
    {
        $cliente = Cliente::findOrFail($id);

        $query = Deuda::where('cliente_id', $id)
            ->with(['venta', 'pagos']);

        if ($request->has('estado') && !empty($request->estado)) {
            $query->where('estado', $request->estado);
        }

        $deudas = $query->orderBy('fecha_venta', 'desc')->paginate(20);
        $totalPendienteCliente = Deuda::where('cliente_id', $id)->pendientes()->sum('monto_deuda');

        return view('deudas.detalle_cliente', compact('cliente', 'deudas', 'totalPendienteCliente'));
    }

    public function show($id)
    {
        $deuda = Deuda::with(['cliente', 'venta', 'pagos.user'])->findOrFail($id);
        return view('deudas.show', compact('deuda'));
    }

    public function historial($id)
    {
        $deuda = Deuda::with(['pagos.user'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'pagos' => $deuda->pagos
        ]);
    }

    public function aplicarPago(Request $request, $id)
    {
        $request->validate([
            'monto_pago' => 'required|numeric|min:0.01',
            'observaciones' => 'nullable|string|max:500'
        ]);

        $deuda = Deuda::findOrFail($id);

        if ($deuda->estado === Deuda::ESTADO_PAGADA) {
            return response()->json([
                'success' => false,
                'message' => 'Esta deuda ya está pagada completamente'
            ], 400);
        }

        $montoPago = floatval($request->monto_pago);

        if ($montoPago > $deuda->monto_deuda) {
            if (abs($montoPago - $deuda->monto_deuda) > 0.01) {
                return response()->json([
                    'success' => false,
                    'message' => 'El monto del pago no puede ser mayor a la deuda pendiente'
                ], 400);
            }
            $montoPago = $deuda->monto_deuda;
        }

        DB::beginTransaction();
        try {
            $cajaAbierta = requireSelectedCaja('registrar el pago');

            $pago = DeudaPago::create([
                'deuda_id' => $deuda->id,
                'user_id' => Auth::id(),
                'cierre_caja_id' => $cajaAbierta ? $cajaAbierta->id : null,
                'caja_id' => $cajaAbierta ? $cajaAbierta->caja_id : null,
                'monto' => $montoPago,
                'fecha_pago' => now(),
                'metodo_pago' => $request->metodo_pago ?? 'Efectivo',
                'referencia' => $request->referencia ?? null,
                'codigo_comprobante' => 'PAY-' . strtoupper(Str::random(8)),
                'observaciones' => $request->observaciones
            ]);

            if ($cajaAbierta) {
                $mP = $request->metodo_pago ?? 'Efectivo';
                $tipoPago = DB::table('tipos_pagos')->where('nombre', $mP)->first();
                $esEfectivo = $tipoPago ? $tipoPago->es_efectivo : ($mP === 'Efectivo' ? 1 : 0);

                if ($esEfectivo) {
                    $cajaAbierta->ingresos = floatval($cajaAbierta->ingresos ?? 0) + $montoPago;
                    $cajaAbierta->save();
                } else {
                    // Pago digital (Plin, Yape, Transferencia) → registrar ingreso en banco
                    $banco = \App\Models\CuentaBancaria::preferidaParaUsuario();
                    if ($banco) {
                        \App\Models\BancoMovimiento::create([
                            'cuenta_bancaria_id' => $banco->id,
                            'user_id' => Auth::id(),
                            'tipo' => 'ingreso',
                            'monto' => $montoPago,
                            'concepto' => 'Cobro deuda - ' . ($deuda->numero_comprobante ?? 'S/N') . ' (' . $mP . ')',
                            'referencia' => $request->referencia ?? null,
                            'fecha' => now()->toDateString(),
                            'sucursal_id' => Auth::user()->branch_id,
                        ]);
                        $banco->increment('saldo_actual', $montoPago);
                    }
                }

                \App\Models\OperacionCaja::create([
                    'cierre_caja_id' => $cajaAbierta->id,
                    'user_id' => Auth::id(),
                    'tipo' => 'ingreso',
                    'partida' => 'Cobro Deuda',
                    'concepto' => 'Pago de deuda - Ticket: ' . $deuda->numero_comprobante,
                    'importe' => $montoPago,
                    'metodo_pago' => $mP,
                    'es_efectivo' => $esEfectivo,
                    'fecha' => now(),
                ]);
            }

            $nuevoMontoPagado = $deuda->monto_pagado + $montoPago;
            $nuevaDeuda = $deuda->monto_total - $nuevoMontoPagado;
            $estado = $nuevaDeuda <= 0.01 ? Deuda::ESTADO_PAGADA : Deuda::ESTADO_PARCIAL;

            $deuda->update([
                'monto_pagado' => $nuevoMontoPagado,
                'monto_deuda' => max(0, $nuevaDeuda),
                'estado' => $estado,
                'observaciones' => $request->observaciones
            ]);

            if ($estado === Deuda::ESTADO_PAGADA && $deuda->venta_id) {
                $venta = Venta::find($deuda->venta_id);
                if ($venta) {
                    $venta->pagado = 1;
                    $venta->save();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pago aplicado correctamente',
                'deuda' => [
                    'monto_deuda' => $deuda->monto_deuda,
                    'estado' => $deuda->estado
                ],
                'pago_id' => $pago->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al aplicar el pago: ' . $e->getMessage()
            ], 500);
        }
    }

    public function aplicarPagoAcumulado(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'monto_pago' => 'required|numeric|min:0.01',
            'metodo_pago' => 'nullable|string',
            'observaciones' => 'nullable|string|max:500'
        ]);

        $montoRestante = floatval($request->monto_pago);
        $montoInicial = $montoRestante;
        $clienteId = $request->cliente_id;
        $cliente = Cliente::findOrFail($clienteId);

        $deudas = Deuda::where('cliente_id', $clienteId)
            ->where('estado', '!=', Deuda::ESTADO_PAGADA)
            ->orderBy('fecha_venta', 'asc')
            ->get();

        if ($deudas->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'El cliente no tiene deudas pendientes'
            ], 400);
        }

        $totalDeuda = $deudas->sum('monto_deuda');
        if ($montoRestante > $totalDeuda + 0.01) {
            return response()->json([
                'success' => false,
                'message' => 'El monto ingresado (S/ ' . number_format($montoRestante, 2) . ') supera la deuda total del cliente (S/ ' . number_format($totalDeuda, 2) . ')'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $cajaAbierta = requireSelectedCaja('registrar el pago acumulado');

            $pagoIds = [];
            $batchId = 'BT-' . strtoupper(Str::random(10));

            foreach ($deudas as $deuda) {
                if ($montoRestante <= 0)
                    break;

                $pagoDeuda = min($montoRestante, $deuda->monto_deuda);

                $pago = DeudaPago::create([
                    'deuda_id' => $deuda->id,
                    'user_id' => Auth::id(),
                    'cierre_caja_id' => $cajaAbierta ? $cajaAbierta->id : null,
                    'caja_id' => $cajaAbierta ? $cajaAbierta->caja_id : null,
                    'monto' => $pagoDeuda,
                    'fecha_pago' => now(),
                    'metodo_pago' => $request->metodo_pago ?? 'Efectivo',
                    'referencia' => $request->referencia ?? null,
                    'codigo_comprobante' => 'PAY-AC-' . strtoupper(Str::random(8)),
                    'batch_id' => $batchId,
                    'observaciones' => $request->observaciones ? "Pago Acumulado: " . $request->observaciones : "Pago acumulado de cliente"
                ]);
                $pagoIds[] = $pago->id;

                $nuevoMontoPagado = $deuda->monto_pagado + $pagoDeuda;
                $nuevaDeudaVal = $deuda->monto_total - $nuevoMontoPagado;
                $estado = $nuevaDeudaVal <= 0.01 ? Deuda::ESTADO_PAGADA : Deuda::ESTADO_PARCIAL;

                $deuda->update([
                    'monto_pagado' => $nuevoMontoPagado,
                    'monto_deuda' => max(0, $nuevaDeudaVal),
                    'estado' => $estado
                ]);

                if ($estado === Deuda::ESTADO_PAGADA && $deuda->venta_id) {
                    $venta = Venta::find($deuda->venta_id);
                    if ($venta) {
                        $venta->pagado = 1;
                        $venta->save();
                    }
                }

                $montoRestante -= $pagoDeuda;
            }

            if ($cajaAbierta) {
                $mP = $request->metodo_pago ?? 'Efectivo';
                $tipoPago = DB::table('tipos_pagos')->where('nombre', $mP)->first();
                $esEfectivo = $tipoPago ? $tipoPago->es_efectivo : ($mP === 'Efectivo' ? 1 : 0);

                if ($esEfectivo) {
                    $cajaAbierta->ingresos = floatval($cajaAbierta->ingresos ?? 0) + $montoInicial;
                    $cajaAbierta->save();
                } else {
                    // Pago digital (Plin, Yape, Transferencia) → registrar ingreso en banco
                    $banco = \App\Models\CuentaBancaria::preferidaParaUsuario();
                    if ($banco) {
                        \App\Models\BancoMovimiento::create([
                            'cuenta_bancaria_id' => $banco->id,
                            'user_id' => Auth::id(),
                            'tipo' => 'ingreso',
                            'monto' => $montoInicial,
                            'concepto' => 'Cobro deuda acumulado - Cliente: ' . $cliente->nombre . ' (' . $mP . ')',
                            'referencia' => $request->referencia ?? null,
                            'fecha' => now()->toDateString(),
                            'sucursal_id' => Auth::user()->branch_id,
                        ]);
                        $banco->increment('saldo_actual', $montoInicial);
                    }
                }

                \App\Models\OperacionCaja::create([
                    'cierre_caja_id' => $cajaAbierta->id,
                    'user_id' => Auth::id(),
                    'tipo' => 'ingreso',
                    'partida' => 'Cobro Deuda',
                    'concepto' => 'Pago acumulado cliente: ' . $cliente->nombre,
                    'importe' => $montoInicial,
                    'metodo_pago' => $mP,
                    'es_efectivo' => $esEfectivo,
                    'fecha' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pago acumulado aplicado correctamente distribuyéndose en ' . $deudas->count() . ' documentos.',
                'pago_ids' => $pagoIds,
                'batch_id' => $batchId
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al aplicar el pago acumulado: ' . $e->getMessage()
            ], 500);
        }
    }

    public function marcarComoPagada($id)
    {
        $deuda = Deuda::findOrFail($id);

        DB::beginTransaction();
        try {
            $deuda->marcarComoPagada();

            if ($deuda->venta_id) {
                $venta = Venta::find($deuda->venta_id);
                if ($venta) {
                    $venta->pagado = 1;
                    $venta->save();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Deuda marcada como pagada correctamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar la deuda como pagada: ' . $e->getMessage()
            ], 500);
        }
    }

    public function generarComprobantePago($pago_id)
    {
        $pago = DeudaPago::with(['deuda.cliente', 'user'])->findOrFail($pago_id);
        $empresa = Company::find(Auth::user()->company_id);
        $cliente = $pago->deuda->cliente;

        // Calcular el saldo total del cliente DESPUÉS del pago (ya está restado en la BD)
        $saldoTotal = $cliente->debe;

        // Check for Logo (Prioritize sucursal)
        $sucursal = $pago->deuda->sucursal;
        $logo = null;
        $logoPath = null;

        // 1. Prioridad: Logo de la sucursal
        if ($sucursal && $sucursal->logo) {
            $path = $sucursal->logo_path;
            if ($path && file_exists($path)) {
                $logoPath = $path;
            }
        }

        // 2. Fallback: Logo de la empresa
        if (!$logoPath && $empresa && $empresa->logo) {
            $path = $empresa->logo_path;
            if ($path && file_exists($path)) {
                $logoPath = $path;
            }
        }

        if ($logoPath && file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoType = pathinfo($logoPath, PATHINFO_EXTENSION);
            $logo = 'data:image/' . $logoType . ';base64,' . $logoData;
        }

        // Check if this payment is part of a batch
        $montoAbonado = $pago->monto;
        $esPagoAcumulado = false;
        
        if ($pago->batch_id) {
            $esPagoAcumulado = true;
            $montoAbonado = DeudaPago::where('batch_id', $pago->batch_id)->sum('monto');
        }

        $data = [
            'pago' => $pago,
            'deuda' => $pago->deuda,
            'cliente' => $cliente,
            'empresa' => $empresa,
            'logo' => $logo,
            'saldoTotal' => $saldoTotal,
            'montoAbonado' => $montoAbonado,
            'esPagoAcumulado' => $esPagoAcumulado
        ];

        $pdf = Pdf::loadView('deudas.comprobante_pago', $data)
            ->setPaper([0, 0, 215, 600], 'portrait');
        return $pdf->stream('recibo_pago_' . $pago->codigo_comprobante . '.pdf');
    }
}
