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
        $query = Deuda::with(['cliente', 'venta'])
            ->where('sucursal_id', 1); // Ajustar según tu lógica de sucursales

        // Filtros
        if ($request->has('cliente_id') && !empty($request->cliente_id)) {
            $query->where('cliente_id', $request->cliente_id);
        }

        if ($request->has('estado') && !empty($request->estado)) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('fecha_desde') && !empty($request->fecha_desde)) {
            $query->whereDate('fecha_venta', '>=', $request->fecha_desde);
        }

        if ($request->has('fecha_hasta') && !empty($request->fecha_hasta)) {
            $query->whereDate('fecha_venta', '<=', $request->fecha_hasta);
        }

        // Solo deudas pendientes por defecto
        if (!$request->has('mostrar_todas')) {
            $query->pendientes();
        }

        $deudas = $query->orderBy('fecha_venta', 'desc')
            ->paginate(20);

        // Para el filtro de clientes
        $clientes = Cliente::where('company_id', $user->company_id)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'numero_documento']);

        // Estadísticas rápidas
        $estadisticas = [
            'total_pendiente' => Deuda::where('sucursal_id', 1)->pendientes()->sum('monto_deuda'),
            'cantidad_pendiente' => Deuda::where('sucursal_id', 1)->pendientes()->count(),
            'vencidas' => Deuda::where('sucursal_id', 1)->vencidas()->count(),
        ];

        return view('deudas.index', compact('deudas', 'clientes', 'estadisticas'));
    }

    public function show($id)
    {
        $deuda = Deuda::with(['cliente', 'venta', 'user', 'pagos.user'])
            ->findOrFail($id);

        return view('deudas.show', compact('deuda'));
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

        if ($montoPago > $deuda->monto_deuda) { // Margen de error por decimales podría ser necesario
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
            // Guardar registro en deuda_pagos
            $pago = DeudaPago::create([
                'deuda_id' => $deuda->id,
                'user_id' => Auth::id(),
                'monto' => $montoPago,
                'fecha_pago' => now(),
                'metodo_pago' => $request->metodo_pago ?? 'Efectivo',
                'referencia' => $request->referencia ?? null,
                'codigo_comprobante' => 'PAY-' . strtoupper(Str::random(8)),
                'observaciones' => $request->observaciones
            ]);

            // Actualizar deuda
            $nuevoMontoPagado = $deuda->monto_pagado + $montoPago;
            $nuevaDeuda = $deuda->monto_total - $nuevoMontoPagado;
            $estado = $nuevaDeuda <= 0.01 ? Deuda::ESTADO_PAGADA : Deuda::ESTADO_PARCIAL;

            $deuda->update([
                'monto_pagado' => $nuevoMontoPagado,
                'monto_deuda' => max(0, $nuevaDeuda),
                'estado' => $estado,
                'observaciones' => $request->observaciones // O concatenar?
            ]);

            // Si la deuda se pagó por completo, actualizar la venta
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

    public function marcarComoPagada($id)
    {
        $deuda = Deuda::findOrFail($id);

        if ($deuda->estado === Deuda::ESTADO_PAGADA) {
            return response()->json([
                'success' => false,
                'message' => 'Esta deuda ya está pagada'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Calcular monto pendiente (lo que se va a pagar)
            $montoAPagar = $deuda->monto_deuda;

            // Crear registro de pago full
            $pago = DeudaPago::create([
                'deuda_id' => $deuda->id,
                'user_id' => Auth::id(),
                'monto' => $montoAPagar,
                'fecha_pago' => now(),
                'metodo_pago' => 'Otros', // O 'Regularizacion'
                'codigo_comprobante' => 'PAY-FULL-' . strtoupper(Str::random(6)),
                'observaciones' => 'Marcado como pagada manualmente'
            ]);

            $deuda->marcarComoPagada();

            // Actualizar venta
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
                'message' => 'Error al marcar como pagada: ' . $e->getMessage()
            ], 500);
        }
    }

    public function reporteDeudas(Request $request)
    {
        $query = Deuda::with(['cliente'])
            ->where('sucursal_id', 1);

        // Filtros para el reporte
        if ($request->has('fecha_desde') && !empty($request->fecha_desde)) {
            $query->whereDate('fecha_venta', '>=', $request->fecha_desde);
        }

        if ($request->has('fecha_hasta') && !empty($request->fecha_hasta)) {
            $query->whereDate('fecha_venta', '<=', $request->fecha_hasta);
        }

        if ($request->has('estado') && !empty($request->estado)) {
            $query->where('estado', $request->estado);
        }

        $deudas = $query->orderBy('fecha_venta', 'desc')->get();

        // Agrupar por cliente
        $deudasPorCliente = $deudas->groupBy('cliente_id')->map(function ($clienteDeudas) {
            $cliente = $clienteDeudas->first()->cliente;
            return [
                'cliente' => $cliente,
                'total_deuda' => $clienteDeudas->sum('monto_deuda'),
                'cantidad_deudas' => $clienteDeudas->count(),
                'deudas' => $clienteDeudas
            ];
        });

        // Totales generales
        $totales = [
            'total_deuda' => $deudas->sum('monto_deuda'),
            'total_pagado' => $deudas->sum('monto_pagado'),
            'cantidad_deudas' => $deudas->count(),
            'clientes_con_deuda' => $deudasPorCliente->count()
        ];

        return view('deudas.reporte', compact('deudasPorCliente', 'totales'));
    }

    public function exportarExcel(Request $request)
    {
        $query = Deuda::with(['cliente'])
            ->where('sucursal_id', 1);

        // Aplicar los mismos filtros que el reporte
        if ($request->has('fecha_desde') && !empty($request->fecha_desde)) {
            $query->whereDate('fecha_venta', '>=', $request->fecha_desde);
        }

        if ($request->has('fecha_hasta') && !empty($request->fecha_hasta)) {
            $query->whereDate('fecha_venta', '<=', $request->fecha_hasta);
        }

        if ($request->has('estado') && !empty($request->estado)) {
            $query->where('estado', $request->estado);
        }

        $deudas = $query->orderBy('fecha_venta', 'desc')->get();

        // Crear contenido CSV
        $csvData = [];
        $csvData[] = [
            'Fecha Venta',
            'Cliente',
            'Documento Cliente',
            'Tipo Comprobante',
            'Número Comprobante',
            'Monto Total',
            'Monto Pagado',
            'Monto Deuda',
            'Estado',
            'Fecha Vencimiento',
            'Días Vencidos',
            'Observaciones'
        ];

        foreach ($deudas as $deuda) {
            $diasVencidos = '';
            if ($deuda->fecha_vencimiento && $deuda->fecha_vencimiento->isPast() && $deuda->estado !== 'pagada') {
                $diasVencidos = $deuda->fecha_vencimiento->diffInDays(now());
            }

            $csvData[] = [
                $deuda->fecha_venta->format('d/m/Y'),
                $deuda->cliente->nombre ?? 'Sin cliente',
                $deuda->cliente->numero_documento ?? '',
                strtoupper($deuda->tipo_documento),
                $deuda->numero_comprobante,
                $deuda->monto_total,
                $deuda->monto_pagado,
                $deuda->monto_deuda,
                ucfirst($deuda->estado),
                $deuda->fecha_vencimiento ? $deuda->fecha_vencimiento->format('d/m/Y') : '',
                $diasVencidos,
                $deuda->observaciones ?? ''
            ];
        }

        $filename = 'reporte_deudas_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($csvData) {
            $file = fopen('php://output', 'w');
            // Agregar BOM para UTF-8
            fwrite($file, "\xEF\xBB\xBF");

            foreach ($csvData as $row) {
                fputcsv($file, $row, ';'); // Usar punto y coma como separador para Excel en español
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function generarComprobantePago($idLayout)
    {
        $pago = DeudaPago::with(['deuda.cliente', 'deuda.venta.company'])->findOrFail($idLayout);
        $deuda = $pago->deuda;
        $cliente = $deuda->cliente;
        $empresa = $deuda->venta->company ?? Company::first();

        // Obtener logo
        $logoBase64 = null;
        if ($empresa && $empresa->logo) {
            $logoFilePath = $empresa->logo_path ?? null;
            if ($logoFilePath && file_exists($logoFilePath)) {
                $logoBase64 = base64_encode(file_get_contents($logoFilePath));
            }
        }
        if (!$logoBase64) {
            // Intenta buscar el default
            $defaultLogoPath = public_path('images/scorpion.png'); // Ajustar según tu proyecto
            if (file_exists($defaultLogoPath)) {
                $logoBase64 = base64_encode(file_get_contents($defaultLogoPath));
            }
        }

        $viewData = [
            'pago' => $pago,
            'deuda' => $deuda,
            'cliente' => $cliente,
            'empresa' => $empresa,
            'logo' => $logoBase64 ? 'data:image/png;base64,' . $logoBase64 : null
        ];

        // Formato ticket 8cm (similar a PosController)
        $customPaper = [0, 0, 226.77, 400]; // Altura puede ser dinámica si se requiere

        $pdf = Pdf::loadView('deudas.comprobante_pago', $viewData)
            ->setPaper($customPaper, 'portrait');

        return $pdf->stream('ticket-pago-' . $pago->id . '.pdf');
    }
}
