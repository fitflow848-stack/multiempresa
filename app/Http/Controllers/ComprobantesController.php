<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\Cliente;
use App\Models\TipoPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ComprobantesController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // Obtener filtros
        $fechaDesde = $request->get('fecha_desde', now()->format('Y-m-d'));
        $fechaHasta = $request->get('fecha_hasta', now()->format('Y-m-d'));
        $cliente = $request->get('cliente', '');
        $tipoDocumento = $request->get('tipo_documento', 'todos');

        // Query base
        $ventasQuery = Venta::where('id_empresa', $company->id)
            ->with(['cliente', 'detalles.producto', 'tipoPago', 'ventaSunat'])
            ->whereBetween('fecha_emision', [
                Carbon::parse($fechaDesde)->startOfDay(),
                Carbon::parse($fechaHasta)->endOfDay()
            ]);

        // Aplicar filtro de cliente
        if (!empty($cliente)) {
            $ventasQuery->whereHas('cliente', function ($q) use ($cliente) {
                $q->where('nombre', 'LIKE', "%$cliente%")
                  ->orWhere('numero_documento', 'LIKE', "%$cliente%");
            });
        }

        // Aplicar filtro de tipo de documento
        if ($tipoDocumento !== 'todos') {
            $ventasQuery->where('tipo_documento', $tipoDocumento);
        }

        $ventas = $ventasQuery->orderBy('fecha_emision', 'desc')
                             ->orderBy('id_venta', 'desc')
                             ->paginate(20);

        // Calcular resumen
        $resumen = $this->calcularResumen($ventasQuery->get());

        // Obtener comprobante seleccionado
        $comprobanteSeleccionado = null;
        $detalleSeleccionado = collect();
        
        $ventaSeleccionadaId = $request->get('venta_id');
        if ($ventaSeleccionadaId) {
            $comprobanteSeleccionado = Venta::with(['cliente', 'detalles.producto', 'tipoPago', 'ventaSunat'])
                ->where('id_venta', $ventaSeleccionadaId)
                ->where('id_empresa', $company->id)
                ->first();
            
            if ($comprobanteSeleccionado) {
                $detalleSeleccionado = $comprobanteSeleccionado->detalles;
            }
        }

        return view('comprobantes.index', compact(
            'user', 
            'company', 
            'ventas', 
            'resumen',
            'fechaDesde',
            'fechaHasta', 
            'cliente', 
            'tipoDocumento',
            'comprobanteSeleccionado',
            'detalleSeleccionado'
        ));
    }

    public function detalle(Request $request, $id)
    {
        $user = Auth::user();
        $company = $user->company;

        $venta = Venta::with(['cliente', 'detalles.producto', 'tipoPago'])
            ->where('id_venta', $id)
            ->where('id_empresa', $company->id)
            ->firstOrFail();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'venta' => $venta,
                'detalles' => $venta->detalles
            ]);
        }

        return redirect()->route('comprobantes.index', ['venta_id' => $id]);
    }

    public function seleccionarTodo(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // Obtener los mismos filtros que en el index
        $fechaDesde = $request->get('fecha_desde', now()->format('Y-m-d'));
        $fechaHasta = $request->get('fecha_hasta', now()->format('Y-m-d'));
        $cliente = $request->get('cliente', '');
        $tipoDocumento = $request->get('tipo_documento', 'todos');

        $ventasQuery = Venta::where('id_empresa', $company->id)
            ->whereBetween('fecha_emision', [
                Carbon::parse($fechaDesde)->startOfDay(),
                Carbon::parse($fechaHasta)->endOfDay()
            ]);

        if (!empty($cliente)) {
            $ventasQuery->whereHas('cliente', function ($q) use ($cliente) {
                $q->where('nombre', 'LIKE', "%$cliente%")
                  ->orWhere('numero_documento', 'LIKE', "%$cliente%");
            });
        }

        if ($tipoDocumento !== 'todos') {
            $ventasQuery->where('tipo_documento', $tipoDocumento);
        }

        $ventas = $ventasQuery->get();
        $resumen = $this->calcularResumen($ventas);

        return response()->json([
            'success' => true,
            'resumen' => $resumen,
            'ventas_ids' => $ventas->pluck('id_venta')
        ]);
    }

    public function cancelar(Request $request)
    {
        // Lógica para cancelar comprobantes seleccionados
        $ventasIds = $request->get('ventas_ids', []);
        $user = Auth::user();
        
        $count = Venta::where('id_empresa', $user->company_id)
            ->whereIn('id_venta', $ventasIds)
            ->update(['estado' => 0]);

        return response()->json([
            'success' => true,
            'message' => "Se cancelaron $count comprobantes",
            'cancelados' => $count
        ]);
    }

    public function devolver(Request $request)
    {
        // Lógica para procesar devoluciones
        $ventasIds = $request->get('ventas_ids', []);
        $user = Auth::user();
        
        // Aquí iría la lógica específica de devolución
        // Por ahora solo marcamos como devuelto
        $count = Venta::where('id_empresa', $user->company_id)
            ->whereIn('id_venta', $ventasIds)
            ->update(['estado' => 3]);

        return response()->json([
            'success' => true,
            'message' => "Se procesaron $count devoluciones",
            'devueltos' => $count
        ]);
    }

    private function calcularResumen($ventas)
    {
        $resumen = [
            'facturas' => 0,
            'boletas' => 0,
            'tickets' => 0,
            'nota_venta' => 0,
            'total_ventas' => 0,
            'importe_efectivo' => 0,
            'importe_cuotas' => 0,
            'total_facturado' => 0,
            'total_boleteado' => 0,
            'importe_seleccionados' => 0,
            'pendiente_seleccionados' => 0
        ];

        foreach ($ventas as $venta) {
            // Contar por tipo de documento
            switch (strtolower($venta->tipo_documento ?? 'ticket')) {
                case 'factura':
                    $resumen['facturas']++;
                    $resumen['total_facturado'] += $venta->total;
                    break;
                case 'boleta':
                    $resumen['boletas']++;
                    $resumen['total_boleteado'] += $venta->total;
                    break;
                case 'nota-venta':
                    $resumen['nota_venta']++;
                    break;
                default:
                    $resumen['tickets']++;
                    break;
            }

            $resumen['total_ventas'] += $venta->total;

            // Importe por tipo de pago
            if ($venta->pagado) {
                $resumen['importe_efectivo'] += $venta->total;
            } else {
                $resumen['importe_cuotas'] += $venta->total;
            }
        }

        return $resumen;
    }

    public function imprimir(Request $request, $id)
    {
        $user = Auth::user();
        $company = $user->company;

        $venta = Venta::with(['cliente', 'detalles.producto', 'tipoPago'])
            ->where('id_venta', $id)
            ->where('id_empresa', $company->id)
            ->firstOrFail();

        // Retornar vista de impresión o PDF
        return view('comprobantes.imprimir', compact('venta', 'company'));
    }
}