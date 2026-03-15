<?php

namespace App\Http\Controllers;

use App\Models\Familia;
use App\Models\User;
use App\Models\Sucursal;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Cotizacion;
use App\Models\Deuda;
use App\Models\CierreCaja;
use App\Models\DeudaPago;
use App\Models\Compra;
use App\Models\CompraLinea;
use App\Models\AlmacenIngresoDetalle;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DynamicReportExport;

class ReporteController extends Controller
{
    public function index()
    {
        // Load data for filters
        $familias = Familia::all();
        $vendedores = User::all();
        $locales = Sucursal::all(); 
        $tiposPago = \App\Models\TipoPago::all();
        $clientes = \App\Models\Cliente::orderBy('nombre')->get();

        // Types of reports key-value fetch from DB
        $reports = DB::table('reports')->where('active', true)->orderBy('id')->get();
        // Group by category to match previous structure
        $reportTypes = [];
        foreach ($reports as $r) {
            $reportTypes[$r->category][$r->id] = $r->name;
        }

        return view('reportes.index', compact('familias', 'vendedores', 'locales', 'reportTypes', 'tiposPago', 'clientes'));
    }

    public function generate(Request $request)
    {
        $reportId = $request->input('report_id');
        $report = DB::table('reports')->find($reportId);

        if ($report && $report->method && method_exists($this, $report->method)) {
            if ($report->method === 'reporteArqueoCajaGeneral') {
                $result = $this->reporteArqueoCaja($request, false);
            } elseif ($report->method === 'reporteArqueoCajaUsuario') {
                $result = $this->reporteArqueoCaja($request, true);
            } elseif ($report->method === 'reporteSuscripcionesPlaceholder') {
                $result = $this->reporteSuscripcionesPlaceholder($request, $reportId);
            } else {
                $result = $this->{$report->method}($request);
            }

            if (is_array($result)) {
                return view($result['view'], $result['data'])->render();
            }
            return $result;
        }

        return '<div class="alert alert-warning">Reporte no implementado o no encontrado</div>';
    }

    public function export(Request $request)
    {
        $reportId = $request->input('report_id');
        $report = DB::table('reports')->find($reportId);
        if (!$report)
            return back()->with('error', 'Reporte no encontrado');

        $result = $this->getReportResult($request, $report);
        if (!is_array($result))
            return back()->with('error', 'Este reporte no soporta exportación dinámica');

        $filename = \Illuminate\Support\Str::slug($report->name) . '_' . date('YmdHis') . '.xlsx';

        return Excel::download(
            new DynamicReportExport($result['view'], $result['data']),
            $filename
        );
    }

    public function pdf(Request $request)
    {
        $reportId = $request->input('report_id');
        $report = DB::table('reports')->find($reportId);
        if (!$report)
            return back()->with('error', 'Reporte no encontrado');

        $result = $this->getReportResult($request, $report);
        if (!is_array($result))
            return back()->with('error', 'Este reporte no soporta impresión dinámica');

        $data = $result['data'];
        $data['pdf_mode'] = true;
        $data['view'] = $result['view'];
        $data['report_title'] = $report->name;

        $pdf = Pdf::loadView('reportes.pdf_layout', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->stream($report->name . '.pdf');
    }

    private function getReportResult(Request $request, $report)
    {
        if ($report->method === 'reporteArqueoCajaGeneral') {
            return $this->reporteArqueoCaja($request, false);
        }
        if ($report->method === 'reporteArqueoCajaUsuario') {
            return $this->reporteArqueoCaja($request, true);
        }
        if ($report->method === 'reporteSuscripcionesPlaceholder') {
            return $this->reporteSuscripcionesPlaceholder($request, $report->id);
        }
        return $this->{$report->method}($request);
    }

    private function reportePorProducto(Request $request)
    {
        // Logic for "Por Producto" - agrupado por comprobante y producto
        $query = VentaDetalle::with(['venta.user', 'producto', 'almacenIngresoDetalle.productoLinea'])
            ->whereHas('venta', function ($q) use ($request) {
                $q->where('estado', '!=', '0');
                // Apply date filters
                if ($request->input('desde'))
                    $q->whereDate('fecha_emision', '>=', $request->input('desde'));
                if ($request->input('hasta'))
                    $q->whereDate('fecha_emision', '<=', $request->input('hasta'));
                // Apply local/seller filters
                if ($request->input('vendedor_id'))
                    $q->where('id_usuario', $request->input('vendedor_id'));
            });

        // Apply family filter on product
        if ($request->input('familia_id')) {
            $query->whereHas('producto', function ($q) use ($request) {
                $q->where('familia_id', $request->input('familia_id'));
            });
        }

        // Apply barcode filter
        if ($request->input('codigo_barras')) {
            $query->whereHas('producto', function ($q) use ($request) {
                $q->where('codigo_barras', 'like', '%' . $request->input('codigo_barras') . '%');
            });
        }

        $detalles = $query->orderBy('id_venta')->get();

        // Agrupar por comprobante y nombre del producto para sumar cantidades
        $agrupados = $detalles->groupBy(function ($item) {
            $nombreBase = $item->producto->nombre ?? $item->nombre_servicio ?? 'Sin nombre';
            $linea = $item->almacenIngresoDetalle->productoLinea ?? null;
            $nombreFull = $nombreBase;
            if ($linea) {
                if ($linea->presentacion) $nombreFull .= ' / ' . $linea->presentacion;
                if ($linea->concentracion) $nombreFull .= ' / ' . $linea->concentracion;
            }
            return $item->id_venta . '|||' . $nombreFull;
        })->map(function ($grupo) {
            $primero = $grupo->first();
            $cantidadTotal = $grupo->sum('cantidad');

            $nombreBase = $primero->producto->nombre ?? $primero->nombre_servicio ?? 'Sin nombre';
            $linea = $primero->almacenIngresoDetalle->productoLinea ?? null;
            $nombreFull = $nombreBase;
            $pres = '';
            $conc = '';
            if ($linea) {
                $pres = $linea->presentacion ?? '';
                $conc = $linea->concentracion ?? '';
                if ($pres) $nombreFull .= ' / ' . $pres;
                if ($conc) $nombreFull .= ' / ' . $conc;
            }

            // Calcular el costo total sumando cada item individualmente
            $costoTotalGrupo = 0;
            foreach ($grupo as $item) {
                $costoItem = 0;
                if ($item->almacenIngresoDetalle && $item->almacenIngresoDetalle->costo > 0) {
                    $costoItem = $item->almacenIngresoDetalle->costo;
                } elseif ($item->producto && $item->producto->precio_compra > 0) {
                    $costoItem = $item->producto->precio_compra;
                }
                $costoTotalGrupo += $costoItem * $item->cantidad;
            }

            $costoUnitario = $cantidadTotal > 0 ? $costoTotalGrupo / $cantidadTotal : 0;
            $subtotalVenta = $grupo->sum('importe'); // total con IGV (si aplica)

            $igvEnLineas = $grupo->sum('igv');
            $venta = $primero->venta;
            $totalVenta = (float) ($venta->total ?? 0);
            $igvVenta = (float) ($venta->igv ?? 0);

            if ($igvEnLineas > 0) {
                $igvGrupo = $igvEnLineas;
                $valorVenta = $subtotalVenta - $igvGrupo;
            } elseif ($totalVenta > 0 && $igvVenta > 0) {
                $igvGrupo = round($igvVenta * ($subtotalVenta / $totalVenta), 2);
                $valorVenta = $subtotalVenta - $igvGrupo;
            } else {
                $igvGrupo = 0;
                $valorVenta = $subtotalVenta;
            }

            // Ganancia = Valor Venta (sin IGV) - Costo (sin IGV)
            $ganancia = $valorVenta - $costoTotalGrupo;

            return (object) [
                'venta' => $primero->venta,
                'producto' => $primero->producto,
                'nombre_completo' => $nombreFull,
                'presentacion' => $pres,
                'concentracion' => $conc,
                'cantidad' => $cantidadTotal,
                'precio_unitario' => $primero->precio_unitario,
                'costo_unitario' => $costoUnitario,
                'subtotal' => $subtotalVenta,
                'valor_venta' => $valorVenta,
                'igv' => $igvGrupo,
                'costo_total' => $costoTotalGrupo,
                'ganancia' => $ganancia,
            ];
        })->values();

        // Ordenar de forma ascendente por nombre del producto completo
        $resultados = $agrupados->sortBy(function($item) {
            return $item->nombre_completo;
        });

        // Calcular totales
        $totales = (object) [
            'cantidad' => $resultados->sum('cantidad'),
            'subtotal' => $resultados->sum('subtotal'),
            'valor_venta' => $resultados->sum('valor_venta'),
            'igv' => $resultados->sum('igv'),
            'costo_total' => $resultados->sum('costo_total'),
            'ganancia' => $resultados->sum('ganancia'),
        ];

        // $resultados ya contiene agrupados ordenado

        return [
            'view' => 'reportes.partials.por_producto',
            'data' => compact('resultados', 'totales')
        ];
    }

    private function reporteClientesFrecuentes(Request $request)
    {
        $query = Venta::select(
            'id_cliente',
            DB::raw('count(*) as total_compras'),
            DB::raw('sum(total) as total_gastado')
        )
            ->where('estado', '!=', '0') // Asumiendo 0 es anulado, ajustar según lógica real
            ->groupBy('id_cliente')
            ->orderByDesc('total_compras');

        // Apply filters
        if ($request->input('desde'))
            $query->whereDate('fecha_emision', '>=', $request->input('desde'));
        if ($request->input('hasta'))
            $query->whereDate('fecha_emision', '<=', $request->input('hasta'));
        if ($request->input('vendedor_id'))
            $query->where('id_usuario', $request->input('vendedor_id')); // Venta tiene id_usuario? Verificar modelo

        $resultados = $query->with('cliente')->limit(100)->get();

        return [
            'view' => 'reportes.partials.clientes_frecuentes',
            'data' => compact('resultados')
        ];
    }

    private function reporteComprobantes(Request $request)
    {
        $query = Venta::with(['cliente.deudas', 'user', 'tipoPago', 'deuda'])
            ->where('estado', '!=', '0')
            ->orderByDesc('fecha_emision');

        if ($request->input('desde'))
            $query->whereDate('fecha_emision', '>=', $request->input('desde'));
        if ($request->input('hasta'))
            $query->whereDate('fecha_emision', '<=', $request->input('hasta'));
        if ($request->input('vendedor_id'))
            $query->where('id_usuario', $request->input('vendedor_id'));

        // El filtro de tipo_comprobante no estaba implementado, vamos a añadirlo si es posible
        if ($request->input('tipo_comprobante')) {
            $tipo = $request->input('tipo_comprobante');
            if ($tipo == 'boleta')
                $query->where('serie', 'LIKE', 'B%');
            elseif ($tipo == 'factura')
                $query->where('serie', 'LIKE', 'F%');
            elseif ($tipo == 'ticket')
                $query->where('serie', 'NOT LIKE', 'B%')->where('serie', 'NOT LIKE', 'F%');
        }

        $resultados = $query->limit(500)->get();

        // Procesar datos para la vista
        $resultados->each(function ($v) {
            if ($v->deuda) {
                $v->monto_pagado_doc = $v->deuda->monto_pagado;
                $v->monto_pendiente_doc = $v->deuda->monto_deuda;
            } else {
                $v->monto_pagado_doc = $v->total;
                $v->monto_pendiente_doc = 0;
            }
            // Deuda total del cliente (atributo calculado en Cliente.php)
            $v->deuda_total_cliente = $v->cliente->debe ?? 0;
        });

        return [
            'view' => 'reportes.partials.comprobantes',
            'data' => compact('resultados')
        ];
    }

    private function reporteCostoMayor(Request $request)
    {
        // Productos donde precio_compra > pvp
        $query = Producto::whereColumn('precio_compra', '>', 'pvp');

        if ($request->input('familia_id'))
            $query->where('familia_id', $request->input('familia_id'));

        $resultados = $query->limit(200)->get();
        return [
            'view' => 'reportes.partials.costo_mayor',
            'data' => compact('resultados')
        ];
    }

    private function reporteVentasUsuario(Request $request)
    {
        // Detalle de ventas agrupado/ordenado por usuario
        $query = Venta::with(['cliente', 'user'])
            ->where('estado', '!=', '0')
            ->orderBy('id_usuario')
            ->orderByDesc('fecha_emision');

        if ($request->input('desde'))
            $query->whereDate('fecha_emision', '>=', $request->input('desde'));
        if ($request->input('hasta'))
            $query->whereDate('fecha_emision', '<=', $request->input('hasta'));
        if ($request->input('vendedor_id'))
            $query->where('id_usuario', $request->input('vendedor_id'));

        $resultados = $query->limit(200)->get();
        return [
            'view' => 'reportes.partials.ventas_usuario',
            'data' => compact('resultados')
        ];
    }

    private function reporteDevoluciones(Request $request)
    {
        // Asumiendo que devoluciones son Notas de Crédito
        $query = Venta::with(['cliente', 'user'])
            ->where('tipo_documento', 'like', '%nota%credito%') // Ajustar valor exacto
            ->orWhere('serie', 'like', 'NC%')
            ->orderByDesc('fecha_emision');

        if ($request->input('desde'))
            $query->whereDate('fecha_emision', '>=', $request->input('desde'));
        if ($request->input('hasta'))
            $query->whereDate('fecha_emision', '<=', $request->input('hasta'));

        $resultados = $query->limit(200)->get();
        return [
            'view' => 'reportes.partials.devoluciones',
            'data' => compact('resultados')
        ];
    }

    private function reportePedidos(Request $request)
    {
        // Cotizaciones
        $query = Cotizacion::with(['cliente', 'usuario'])
            ->orderByDesc('fecha');

        if ($request->input('desde'))
            $query->whereDate('fecha', '>=', $request->input('desde'));
        if ($request->input('hasta'))
            $query->whereDate('fecha', '<=', $request->input('hasta'));

        $resultados = $query->limit(200)->get();
        return [
            'view' => 'reportes.partials.pedidos',
            'data' => compact('resultados')
        ];
    }

    private function reportePorClientes(Request $request)
    {
        // Ventas listadas por cliente
        $query = Venta::with(['cliente', 'user', 'tipoPago'])
            ->where('estado', '!=', '0')
            ->orderBy('id_cliente')
            ->orderByDesc('fecha_emision');

        if ($request->input('desde'))
            $query->whereDate('fecha_emision', '>=', $request->input('desde'));
        if ($request->input('hasta'))
            $query->whereDate('fecha_emision', '<=', $request->input('hasta'));
        if ($request->input('vendedor_id'))
            $query->where('id_usuario', $request->input('vendedor_id'));

        $resultados = $query->limit(200)->get();
        return [
            'view' => 'reportes.partials.por_clientes',
            'data' => compact('resultados')
        ];
    }

    private function reportePorClientesConsolidado(Request $request)
    {
        // Ventas sumadas por cliente
        $query = Venta::select(
            'id_cliente',
            DB::raw('count(*) as total_transacciones'),
            DB::raw('sum(total) as monto_total')
        )
            ->where('estado', '!=', '0')
            ->groupBy('id_cliente')
            ->orderByDesc('monto_total');

        if ($request->input('desde'))
            $query->whereDate('fecha_emision', '>=', $request->input('desde'));
        if ($request->input('hasta'))
            $query->whereDate('fecha_emision', '<=', $request->input('hasta'));
        if ($request->input('vendedor_id'))
            $query->where('id_usuario', $request->input('vendedor_id'));

        $resultados = $query->with('cliente')->limit(200)->get();
        return [
            'view' => 'reportes.partials.por_clientes_consolidado',
            'data' => compact('resultados')
        ];
    }

    private function reportePorCobrar(Request $request)
    {
        // Deudas pendientes
        $query = Deuda::withoutGlobalScope('sucursal')
            ->with(['cliente', 'venta'])
            ->where('monto_deuda', '>', 0)
            ->whereNotIn('estado', ['pagada', 'anulada'])
            ->orderByDesc('fecha_venta');

        if ($request->input('desde'))
            $query->whereDate('fecha_venta', '>=', $request->input('desde'));
        if ($request->input('hasta'))
            $query->whereDate('fecha_venta', '<=', $request->input('hasta'));
        
        // Filtro por sucursal (local)
        if ($request->input('local_id')) {
            $query->where('sucursal_id', $request->input('local_id'));
        } else {
            // Si no se especifica local, filtrar por la empresa del usuario
            $query->where('company_id', Auth::user()->company_id);
        }

        // Filtro por cliente
        if ($request->input('cliente_id')) {
            $query->where('cliente_id', $request->input('cliente_id'));
        }

        // Filtro por vendedor
        if ($request->input('vendedor_id')) {
            $query->where('user_id', $request->input('vendedor_id'));
        }

        $resultados = $query->limit(500)->get();
        return [
            'view' => 'reportes.partials.por_cobrar',
            'data' => compact('resultados')
        ];
    }

    private function reportePorCobrarConsolidado(Request $request)
    {
        // Deudas pendientes agrupadas por cliente
        $query = Deuda::withoutGlobalScope('sucursal')
            ->select(
                'cliente_id',
                DB::raw('count(*) as total_documentos'),
                DB::raw('sum(monto_deuda) as total_deuda'),
                DB::raw('min(fecha_vencimiento) as vencimiento_mas_antiguo')
            )
            ->where('monto_deuda', '>', 0)
            ->whereNotIn('estado', ['pagada', 'anulada'])
            ->groupBy('cliente_id')
            ->orderByDesc('total_deuda');

        if ($request->input('desde'))
            $query->whereDate('fecha_venta', '>=', $request->input('desde'));
        if ($request->input('hasta'))
            $query->whereDate('fecha_venta', '<=', $request->input('hasta'));

        // Filtro por sucursal (local)
        if ($request->input('local_id')) {
            $query->where('sucursal_id', $request->input('local_id'));
        } else {
            $query->where('company_id', Auth::user()->company_id);
        }

        if ($request->input('vendedor_id')) {
            $query->where('user_id', $request->input('vendedor_id'));
        }

        if ($request->input('cliente_id')) {
            $query->where('cliente_id', $request->input('cliente_id'));
        }

        $resultados = $query->with('cliente')->limit(500)->get();
        return [
            'view' => 'reportes.partials.por_cobrar_consolidado',
            'data' => compact('resultados')
        ];
    }

    private function reportePorServicio(Request $request)
    {
        // Ventas de productos que son servicios (Unidad 'ZZ' o similar)
        $query = VentaDetalle::with(['venta.cliente', 'producto', 'venta.user'])
            ->whereHas('venta', function ($q) use ($request) {
                if ($request->input('desde'))
                    $q->whereDate('fecha_emision', '>=', $request->input('desde'));
                if ($request->input('hasta'))
                    $q->whereDate('fecha_emision', '<=', $request->input('hasta'));
                if ($request->input('vendedor_id'))
                    $q->where('user_id', $request->input('vendedor_id'));
            })
            ->whereHas('producto.unidadMedida', function ($q) {
                $q->where('codigo', 'ZZ')
                    ->orWhere('nombre', 'LIKE', '%SERVICIO%');
            });

        $resultados = $query->limit(200)->get();
        return [
            'view' => 'reportes.partials.por_servicio',
            'data' => compact('resultados')
        ];
    }

    private function reportePorUsuario(Request $request)
    {
        // Ventas agrupadas por usuario (creador)
        $query = Venta::select(
            'id_usuario',
            DB::raw('count(*) as total_ventas'),
            DB::raw('sum(total) as monto_total')
        )
            ->where('estado', '!=', '0')
            ->groupBy('id_usuario')
            ->orderByDesc('monto_total');

        if ($request->input('desde'))
            $query->whereDate('fecha_emision', '>=', $request->input('desde'));
        if ($request->input('hasta'))
            $query->whereDate('fecha_emision', '<=', $request->input('hasta'));

        $resultados = $query->with('user')->get();
        return [
            'view' => 'reportes.partials.por_usuario',
            'data' => compact('resultados')
        ];
    }

    private function reportePorVendedor(Request $request)
    {
        // Lo mismo que por usuario por ahora
        return $this->reportePorUsuario($request);
    }

    private function reporteMayorMovimiento(Request $request)
    {
        // Productos más vendidos (cantidad)
        $query = VentaDetalle::select(
            'servicio_id',
            DB::raw('sum(cantidad) as total_cantidad'),
            DB::raw('sum(importe) as total_venta')
        )
            ->whereHas('venta', function ($q) use ($request) {
                $q->where('estado', '!=', '0');
                if ($request->input('desde'))
                    $q->whereDate('fecha_emision', '>=', $request->input('desde'));
                if ($request->input('hasta'))
                    $q->whereDate('fecha_emision', '<=', $request->input('hasta'));
                if ($request->input('vendedor_id'))
                    $q->where('id_usuario', $request->input('vendedor_id'));
            })
            ->groupBy('servicio_id')
            ->orderByDesc('total_cantidad');

        $resultados = $query->with('producto')->limit(50)->get();
        return [
            'view' => 'reportes.partials.mayor_movimiento',
            'data' => compact('resultados')
        ];
    }

    private function reporteMayorUtilidad(Request $request)
    {
        // Productos con mayor margen (PVP - Precio Compra) * Cantidad Vendida
        // O simplemente margen unitario? "Mayor Utilidad" suele ser ganancia total generada.
        // Vamos a calcular: Suma((Precio Venta - Costo Prod) * Cantidad)

        // Como el costo no se guarda en venta_detalle, usamos el actual del producto (limitación conocida)
        $query = VentaDetalle::select(
            'servicio_id',
            DB::raw('sum(cantidad) as total_cantidad'),
            DB::raw('sum(importe) as total_venta')
        )
            ->whereHas('venta', function ($q) use ($request) {
                $q->where('estado', '!=', '0');
                if ($request->input('desde'))
                    $q->whereDate('fecha_emision', '>=', $request->input('desde'));
                if ($request->input('hasta'))
                    $q->whereDate('fecha_emision', '<=', $request->input('hasta'));
                if ($request->input('vendedor_id'))
                    $q->where('id_usuario', $request->input('vendedor_id'));
            })
            ->groupBy('servicio_id');
        // El ordenamiento se hará en colección porque necesitamos el costo del producto

        $detalles = $query->with('producto')->get();

        // Calcular utilidad y ordenar
        $resultados = $detalles->map(function ($detalle) {
            $costo = $detalle->producto->precio_compra ?? 0;
            // Estimado: Asumiendo que todo se vendió al precio promedio
            // Utilidad = Total Venta - (Costo * Cantidad)
            $detalle->utilidad_estimada = $detalle->total_venta - ($costo * $detalle->total_cantidad);
            return $detalle;
        })
            ->sortByDesc('utilidad_estimada')
            ->take(50); // Top 50

        return [
            'view' => 'reportes.partials.mayor_utilidad',
            'data' => compact('resultados')
        ];
    }

    private function reporteArqueoCaja(Request $request, $porUsuario = false)
    {
        // Cierres de Caja
        $query = CierreCaja::with('user')
            ->orderByDesc('fecha_cierre');

        if ($request->input('desde'))
            $query->whereDate('fecha_cierre', '>=', $request->input('desde'));
        if ($request->input('hasta'))
            $query->whereDate('fecha_cierre', '<=', $request->input('hasta'));

        if ($porUsuario || $request->input('vendedor_id')) {
            if ($request->input('vendedor_id')) {
                $query->where('user_id', $request->input('vendedor_id'));
            }
            // Si es por usuario pero no hay filtro, mostrar todos desglosados (que es el default)
        }

        $resultados = $query->limit(100)->get();
        return [
            'view' => 'reportes.partials.arqueo_caja',
            'data' => compact('resultados', 'porUsuario')
        ];
    }

    private function reportePagosCliente(Request $request)
    {
        // Historial de Pagos de Deudas
        $query = DeudaPago::with(['deuda.cliente', 'user'])
            ->orderByDesc('fecha_pago');

        if ($request->input('desde'))
            $query->whereDate('fecha_pago', '>=', $request->input('desde'));
        if ($request->input('hasta'))
            $query->whereDate('fecha_pago', '<=', $request->input('hasta'));
        if ($request->input('vendedor_id'))
            $query->where('user_id', $request->input('vendedor_id'));

        $resultados = $query->limit(200)->get();
        return [
            'view' => 'reportes.partials.pagos_cliente',
            'data' => compact('resultados')
        ];
    }

    private function reporteSuscripcionesPlaceholder(Request $request, $id)
    {
        // Placeholder para módulo de suscripciones no implementado
        $titles = [
            '20' => 'SUSCRIPCIONES - COMPROBANTES',
            '21' => 'SUSCRIPCIONES - INCIDENCIAS',
            '22' => 'SUSCRIPCIONES - INICIO OPERACIONES',
            '23' => 'SUSCRIPCIONES - PAGOS',
            '24' => 'LISTADO DE SUSCRIPCIONES',
        ];

        $titulo = $titles[$id] ?? 'REPORTE DE SUSCRIPCIONES';

        return [
            'view' => 'reportes.partials.suscripciones_placeholder',
            'data' => compact('titulo')
        ];
    }

    private function reportePromociones(Request $request)
    {
        // Productos con descuento (pvp_dto < pvp y pvp_dto > 0) OR simplemente pvp_dto > 0 dependiendo de la lógica de negocio.
        // Asolumiremos que si hay un precio de descuento definido, está en promoción.
        $query = Producto::where('pvp_dto', '>', 0)
            ->whereColumn('pvp_dto', '<', 'pvp');

        if ($request->input('familia_id'))
            $query->where('familia_id', $request->input('familia_id'));

        $resultados = $query->limit(100)->get();
        return [
            'view' => 'reportes.partials.promociones',
            'data' => compact('resultados')
        ];
    }

    private function reporteCondicionVenta(Request $request)
    {
        // Productos con condición de venta específica (Ej: con receta médica)
        // Se obtiene de AlmacenIngresoDetalle para tener el precio real y el stock
        $query = AlmacenIngresoDetalle::with(['producto.familia', 'producto.laboratorio'])
            ->whereHas('producto', function ($q) {
                $q->whereNotNull('condicion_venta')
                    ->where('condicion_venta', '!=', '')
                    ->where('condicion_venta', '!=', 'Sin Receta Médica');
            });

        if ($request->input('familia_id')) {
            $query->whereHas('producto', function ($q) use ($request) {
                $q->where('familia_id', $request->input('familia_id'));
            });
        }

        $resultados = $query->limit(200)->get();
        return [
            'view' => 'reportes.partials.productos_condicion',
            'data' => compact('resultados')
        ];
    }

    private function reporteRegistroSanitario(Request $request)
    {
        // Productos con Registro Sanitario ingresado
        $query = Producto::whereNotNull('registro_sanitario')
            ->where('registro_sanitario', '!=', '');

        if ($request->input('familia_id'))
            $query->where('familia_id', $request->input('familia_id'));

        $resultados = $query->limit(100)->get();
        return [
            'view' => 'reportes.partials.productos_sanitario',
            'data' => compact('resultados')
        ];
    }

    private function reporteClientesCambiadosPlaceholder(Request $request)
    {
        // TODO: Definir lógica "Clientes Cambiados"
        // Posiblemente ventas donde el nombre del cliente en la venta difiere del nombre en la tabla clientes
        return [
            'view' => 'reportes.partials.clientes_cambiados_placeholder',
            'data' => []
        ];
    }

    private function reporteFacturadosPorLote(Request $request)
    {
        $query = VentaDetalle::select(
            'almacen_ingreso_detalle.lote',
            'venta_detalles.servicio_id', // Producto
            DB::raw('sum(venta_detalles.cantidad) as total_cantidad'),
            DB::raw('sum(venta_detalles.importe) as total_venta')
        )
            ->leftJoin('almacen_ingreso_detalle', 'venta_detalles.almacen_ingreso_detalle_id', '=', 'almacen_ingreso_detalle.id')
            ->join('ventas', 'venta_detalles.id_venta', '=', 'ventas.id_venta')
            ->where('ventas.estado', '!=', '0')
            ->groupBy('almacen_ingreso_detalle.lote', 'venta_detalles.servicio_id')
            ->orderBy('almacen_ingreso_detalle.lote');

        if ($request->input('desde'))
            $query->whereDate('ventas.fecha_emision', '>=', $request->input('desde'));
        if ($request->input('hasta'))
            $query->whereDate('ventas.fecha_emision', '<=', $request->input('hasta'));

        $resultados = $query->with('producto')->limit(100)->get();
        return [
            'view' => 'reportes.partials.facturados_lote',
            'data' => compact('resultados')
        ];
    }

    private function reporteCompras(Request $request)
    {
        // Listado de compras (ingresos)
        $query = Compra::with(['proveedor', 'usuario', 'lineas.producto'])
            ->orderByDesc('fecha_emision');

        if ($request->input('desde'))
            $query->whereDate('fecha_emision', '>=', $request->input('desde'));
        if ($request->input('hasta'))
            $query->whereDate('fecha_emision', '<=', $request->input('hasta'));

        // Filtro por sucursal (local) usando el almacén de destino
        if ($request->input('local_id')) {
            $query->where('local_destino', $request->input('local_id'));
        }

        $resultados = $query->limit(200)->get();
        return [
            'view' => 'reportes.partials.compras',
            'data' => compact('resultados')
        ];
    }

    private function reporteComprasPorProducto(Request $request)
    {
        // Compras consolidadas por producto
        $query = CompraLinea::select(
            'product_id',
            DB::raw('sum(cantidad) as total_cantidad'),
            DB::raw('sum(costo * cantidad) as total_costo') // Costo total
        )
            ->join('compras', 'compra_lineas.compra_id', '=', 'compras.id')
            ->groupBy('product_id')
            ->orderByDesc('total_cantidad');

        if ($request->input('desde'))
            $query->whereDate('compras.fecha_emision', '>=', $request->input('desde'));
        if ($request->input('hasta'))
            $query->whereDate('compras.fecha_emision', '<=', $request->input('hasta'));

        // Filtro por sucursal (local) de la compra
        if ($request->input('local_id')) {
            $query->where('compras.local_destino', $request->input('local_id'));
        }

        $resultados = $query->with('producto')->limit(100)->get();
        return [
            'view' => 'reportes.partials.compras_producto',
            'data' => compact('resultados')
        ];
    }

    private function reporteBusquedaLoteCompras(Request $request)
    {
        $search = $request->input('busqueda');
        $query = AlmacenIngresoDetalle::with(['producto', 'ingreso'])
            ->whereNotNull('lote')
            ->where('lote', '!=', '');

        if ($search) {
            $query->where('lote', 'LIKE', '%' . $search . '%');
        } else {
            $query->orderBy('id', 'desc');
        }

        $resultados = $query->limit(50)->get();
        return [
            'view' => 'reportes.partials.busqueda_lote_compras',
            'data' => compact('resultados', 'search')
        ];
    }

    private function reporteBusquedaLoteVentas(Request $request)
    {
        $search = $request->input('busqueda');
        $query = VentaDetalle::select(
            'venta_detalles.*',
            'almacen_ingreso_detalle.lote'
        )
            ->join('almacen_ingreso_detalle', 'venta_detalles.almacen_ingreso_detalle_id', '=', 'almacen_ingreso_detalle.id')
            ->join('ventas', 'venta_detalles.id_venta', '=', 'ventas.id_venta')
            ->with(['venta', 'producto'])
            ->where('ventas.estado', '!=', '0');

        if ($search) {
            $query->where('almacen_ingreso_detalle.lote', 'LIKE', '%' . $search . '%');
        } else {
            $query->whereNotNull('almacen_ingreso_detalle.lote')
                ->orderBy('venta_detalles.id', 'desc');
        }

        $resultados = $query->limit(50)->get();
        return [
            'view' => 'reportes.partials.busqueda_lote_ventas',
            'data' => compact('resultados', 'search')
        ];
    }

    private function reporteBusquedaSerieCompras(Request $request)
    {
        return [
            'view' => 'reportes.partials.busqueda_serie_placeholder',
            'data' => ['tipo' => 'COMPRAS']
        ];
    }

    private function reporteBusquedaSerieVentas(Request $request)
    {
        return [
            'view' => 'reportes.partials.busqueda_serie_placeholder',
            'data' => ['tipo' => 'VENTAS']
        ];
    }


    private function reporteCapitalActualCompra(Request $request)
    {
        $user = Auth::user();
        
        // Base query with scoping
        $baseQuery = AlmacenIngresoDetalle::withoutGlobalScopes()
            ->where('almacen_ingreso_detalle.cantidad', '>', 0)
            ->whereHas('ingreso', function ($q) use ($user, $request) {
                $q->withoutGlobalScopes()
                  ->where('empresa_id', $user->company_id);
                if ($request->input('local_id')) {
                    $q->where('sucursal_id', $request->input('local_id'));
                }
            });

        // Apply product filters
        if ($request->input('familia_id')) {
            $baseQuery->whereHas('producto', function ($q) use ($request) {
                $q->where('familia_id', $request->input('familia_id'));
            });
        }

        if ($request->input('codigo_barras')) {
            $baseQuery->whereHas('producto', function ($q) use ($request) {
                $q->where('codigo_barras', 'like', '%' . $request->input('codigo_barras') . '%');
            });
        }

        $totalQuery = clone $baseQuery;
        $total = $totalQuery->select(
                DB::raw('SUM(almacen_ingreso_detalle.cantidad * almacen_ingreso_detalle.costo) as total_capital')
            )->first()->total_capital ?? 0;

        $detalles = $baseQuery
            ->with(['producto.marca', 'producto.familia', 'producto.laboratorio'])
            ->select(
                'almacen_ingreso_detalle.producto_id',
                DB::raw('SUM(almacen_ingreso_detalle.cantidad) as stock'),
                DB::raw('SUM(almacen_ingreso_detalle.cantidad * almacen_ingreso_detalle.costo) as valor'),
                DB::raw('AVG(almacen_ingreso_detalle.costo) as costo_unitario_promedio')
            )
            ->groupBy('almacen_ingreso_detalle.producto_id')
            ->orderByDesc('valor')
            ->limit(200)
            ->get();

        return [
            'view' => 'reportes.partials.capital_compra',
            'data' => compact('total', 'detalles')
        ];
    }

    private function reporteCapitalActualPromedio(Request $request)
    {
        $user = Auth::user();
        
        $baseQuery = AlmacenIngresoDetalle::withoutGlobalScopes()
            ->where('almacen_ingreso_detalle.cantidad', '>', 0)
            ->whereHas('ingreso', function ($q) use ($user, $request) {
                $q->withoutGlobalScopes()
                  ->where('empresa_id', $user->company_id);
                if ($request->input('local_id')) {
                    $q->where('sucursal_id', $request->input('local_id'));
                }
            });

        if ($request->input('familia_id')) {
            $baseQuery->whereHas('producto', function ($q) use ($request) {
                $q->where('familia_id', $request->input('familia_id'));
            });
        }

        $totalQuery = clone $baseQuery;
        $total = $totalQuery->select(
                DB::raw('SUM(almacen_ingreso_detalle.cantidad * almacen_ingreso_detalle.costo) as total_capital')
            )->first()->total_capital ?? 0;

        $detalles = $baseQuery
            ->with('producto')
            ->select('almacen_ingreso_detalle.producto_id', DB::raw('SUM(almacen_ingreso_detalle.cantidad) as stock'), DB::raw('SUM(almacen_ingreso_detalle.cantidad * almacen_ingreso_detalle.costo) as valor'))
            ->groupBy('almacen_ingreso_detalle.producto_id')
            ->orderByDesc('valor')
            ->limit(100)
            ->get();

        return [
            'view' => 'reportes.partials.capital_promedio',
            'data' => compact('total', 'detalles')
        ];
    }

    private function reporteProductosCostoMayorPrecio(Request $request)
    {
        $user = Auth::user();
        $resultados = AlmacenIngresoDetalle::where('almacen_ingreso_detalle.cantidad', '>', 0)
            ->whereColumn('almacen_ingreso_detalle.costo', '>', 'almacen_ingreso_detalle.pvp')
            ->join('almacen_ingresos', 'almacen_ingreso_detalle.ingreso_id', '=', 'almacen_ingresos.id')
            ->where('almacen_ingresos.empresa_id', $user->company_id)
            ->with(['producto', 'ingreso'])
            ->select('almacen_ingreso_detalle.*') // Solo seleccionar columnas de la tabla principal
            ->limit(100)
            ->get();
        return [
            'view' => 'reportes.partials.productos_costo_mayor',
            'data' => compact('resultados')
        ];
    }

    private function reporteSaldosPorPedido(Request $request)
    {
        return [
            'view' => 'reportes.partials.saldos_pedido_placeholder',
            'data' => []
        ];
    }

    private function reporteStockConsolidado(Request $request)
    {
        $user = Auth::user();
        $sucursalId = $request->input('local_id') ?: null;

        $query = AlmacenIngresoDetalle::withoutGlobalScopes()
            ->where('almacen_ingreso_detalle.cantidad', '>', 0)
            ->whereHas('ingreso', function ($q) use ($user, $sucursalId) {
                $q->withoutGlobalScopes()
                  ->where('empresa_id', $user->company_id);
                if ($sucursalId) {
                    $q->where('sucursal_id', $sucursalId);
                }
            })
            ->select('almacen_ingreso_detalle.producto_id', DB::raw('SUM(almacen_ingreso_detalle.cantidad) as stock_total'))
            ->groupBy('almacen_ingreso_detalle.producto_id')
            ->orderByDesc('stock_total')
            ->with('producto');

        if ($request->input('familia_id')) {
            $query->whereHas('producto', function ($q) use ($request) {
                $q->where('familia_id', $request->input('familia_id'));
            });
        }

        $resultados = $query->limit(200)->get();
        return [
            'view' => 'reportes.partials.stock_consolidado',
            'data' => compact('resultados')
        ];
    }

    private function reporteStockPorLocal(Request $request)
    {
        $user = Auth::user();
        $sucursalId = $request->input('local_id') ?: null;

        $query = AlmacenIngresoDetalle::withoutGlobalScopes()->where('almacen_ingreso_detalle.cantidad', '>', 0)
            ->whereHas('ingreso', function ($q) use ($user, $sucursalId) {
                $q->withoutGlobalScopes()->where('empresa_id', $user->company_id);
                if ($sucursalId) {
                    $q->where('sucursal_id', $sucursalId);
                }
            })
            ->select('almacen_ingreso_detalle.producto_id', 'almacen_ingresos.sucursal_id', DB::raw('SUM(almacen_ingreso_detalle.cantidad) as stock_total'))
            ->join('almacen_ingresos', 'almacen_ingreso_detalle.ingreso_id', '=', 'almacen_ingresos.id')
            ->groupBy('almacen_ingreso_detalle.producto_id', 'almacen_ingresos.sucursal_id')
            ->with('producto');

        if ($request->input('familia_id')) {
            $query->whereHas('producto', function ($q) use ($request) {
                $q->where('familia_id', $request->input('familia_id'));
            });
        }

        $resultados = $query->limit(100)->get();
        return [
            'view' => 'reportes.partials.stock_local',
            'data' => compact('resultados')
        ];
    }

    private function reporteStockPorReferencia(Request $request)
    {
        $user = Auth::user();
        $sucursalId = $request->input('local_id') ?: null;

        $query = AlmacenIngresoDetalle::withoutGlobalScopes()->where('almacen_ingreso_detalle.cantidad', '>', 0)
            ->whereHas('ingreso', function ($q) use ($user, $sucursalId) {
                $q->withoutGlobalScopes()->where('empresa_id', $user->company_id);
                if ($sucursalId) {
                    $q->where('sucursal_id', $sucursalId);
                }
            })
            ->with('producto')
            ->select('almacen_ingreso_detalle.*')
            ->orderBy('almacen_ingreso_detalle.producto_id')
            ->orderBy('almacen_ingreso_detalle.lote');

        if ($request->input('familia_id')) {
            $query->whereHas('producto', function ($q) use ($request) {
                $q->where('familia_id', $request->input('familia_id'));
            });
        }

        $resultados = $query->limit(100)->get();
        return [
            'view' => 'reportes.partials.stock_referencia',
            'data' => compact('resultados')
        ];
    }

    private function reporteTraslados(Request $request)
    {
        return [
            'view' => 'reportes.partials.traslados_placeholder',
            'data' => []
        ];
    }

    private function reportePorVencer(Request $request)
    {
        $user = Auth::user();
        
        // Priorizar filtro por días si existe, sino usar meses
        $diasVencer = $request->input('dias_vencer');
        if ($diasVencer) {
            $fechaLimite = now()->addDays((int)$diasVencer);
            $meses = round((int)$diasVencer / 30, 1);
        } else {
            $meses = $request->input('meses', 3);
            $fechaLimite = now()->addMonths((int)$meses);
        }

        $sucursalId = $request->input('local_id') ?: null;
        $query = AlmacenIngresoDetalle::withoutGlobalScopes()
            ->with(['producto.familia', 'ingreso'])
            ->whereHas('ingreso', function ($q) use ($user, $sucursalId) {
                $q->withoutGlobalScopes()
                  ->where('empresa_id', $user->company_id);
                if ($sucursalId) {
                    $q->where('sucursal_id', $sucursalId);
                }
            })
            ->where('almacen_ingreso_detalle.cantidad', '>', 0)
            ->whereNotNull('almacen_ingreso_detalle.fecha_vencimiento')
            ->whereDate('almacen_ingreso_detalle.fecha_vencimiento', '<=', $fechaLimite)
            ->select('almacen_ingreso_detalle.*')
            ->orderBy('almacen_ingreso_detalle.fecha_vencimiento', 'asc');

        if ($request->input('familia_id')) {
            $query->whereHas('producto', function ($q) use ($request) {
                $q->where('familia_id', $request->input('familia_id'));
            });
        }

        $resultados = $query->limit(500)->get();
        return [
            'view' => 'reportes.partials.por_vencer',
            'data' => compact('resultados', 'meses')
        ];
    }

    private function reporteMovimientosCaja(Request $request)
    {
        // Consolidar ventas y operaciones de caja
        $queryVentas = DB::table('ventas as v')
            ->select(
                'v.created_at as fecha',
                'cj.nombre as caja',
                DB::raw("'Ingreso - Venta' as operacion"),
                DB::raw("'ingreso' as tipo"),
                'c.nombre as detalle',
                DB::raw("CONCAT(v.serie, ' ', v.numero) as concepto"),
                'tp.nombre as metodo_pago',
                'v.total as importe',
                'u.name as usuario'
            )
            ->join('clientes as c', 'c.id', '=', 'v.id_cliente')
            ->join('users as u', 'u.id', '=', 'v.id_usuario')
            ->leftJoin('tipos_pagos as tp', 'tp.id', '=', 'v.id_tipo_pago')
            ->leftJoin('cierre_cajas as cc', 'cc.id', '=', 'v.cierre_caja_id')
            ->leftJoin('cajas as cj', 'cj.id', '=', 'cc.caja_id')
            ->where('v.estado', '!=', '0');

        $localId = $request->input('local_id');

        $queryOperaciones = DB::table('operaciones_caja as o')
            ->select(
                'o.created_at as fecha',
                'cj.nombre as caja',
                'o.partida as operacion',
                'o.tipo as tipo',
                DB::raw("o.tipo as detalle"),
                'o.concepto',
                DB::raw("'Efectivo' as metodo_pago"),
                'o.importe',
                'u.name as usuario'
            )
            ->join('users as u', 'u.id', '=', 'o.user_id')
            ->leftJoin('cierre_cajas as cc', 'cc.id', '=', 'o.cierre_caja_id')
            ->leftJoin('cajas as cj', 'cj.id', '=', 'cc.caja_id');

        if ($localId) {
            // Filtrar ventas por sucursal del cierre de caja asociado
            $queryVentas->where('cc.sucursal_id', $localId);

            // Filtrar operaciones de caja por sucursal directa o del cierre asociado
            $queryOperaciones->where(function ($q) use ($localId) {
                $q->where('o.sucursal_id', $localId)
                  ->orWhere('cc.sucursal_id', $localId);
            });
        }

        if ($request->input('desde')) {
            $queryVentas->whereDate('v.fecha_emision', '>=', $request->input('desde'));
            $queryOperaciones->whereDate('o.created_at', '>=', $request->input('desde'));
        }
        if ($request->input('hasta')) {
            $queryVentas->whereDate('v.fecha_emision', '<=', $request->input('hasta'));
            $queryOperaciones->whereDate('o.created_at', '<=', $request->input('hasta'));
        }
        if ($request->input('vendedor_id')) {
            $queryVentas->where('v.id_usuario', $request->input('vendedor_id'));
            $queryOperaciones->where('o.user_id', $request->input('vendedor_id'));
        }

        $resultados = $queryVentas->union($queryOperaciones)
            ->orderBy('fecha', 'desc')
            ->limit(1000)
            ->get();

        return [
            'view' => 'reportes.partials.movimientos_caja',
            'data' => compact('resultados')
        ];
    }

    private function reporteTransferencias(Request $request)
    {
        $query = \App\Models\AlmacenTransferencia::with(['producto', 'sucursalOrigen', 'sucursalDestino', 'user'])
            ->orderByDesc('created_at');

        if ($request->input('desde'))
            $query->whereDate('created_at', '>=', $request->input('desde'));
        if ($request->input('hasta'))
            $query->whereDate('created_at', '<=', $request->input('hasta'));
        if ($request->input('local_id')) {
            $localId = $request->input('local_id');
            $query->where(function ($q) use ($localId) {
                $q->where('sucursal_origen_id', $localId)
                    ->orWhere('sucursal_destino_id', $localId);
            });
        }

        $resultados = $query->get();

        return [
            'view' => 'reportes.partials.transferencias',
            'data' => compact('resultados')
        ];
    }

    private function reporteMovimientosBoveda(Request $request)
    {
        $query = \App\Models\OperacionCaja::whereHas('cierre.caja', function ($q) {
            $q->where('is_boveda', 1);
        })->with(['user', 'cierre.caja'])
            ->orderByDesc('created_at');

        if ($request->input('desde'))
            $query->whereDate('created_at', '>=', $request->input('desde'));
        if ($request->input('hasta'))
            $query->whereDate('created_at', '<=', $request->input('hasta'));
        if ($request->input('local_id'))
            $query->where('sucursal_id', $request->input('local_id'));

        $resultados = $query->get();

        return [
            'view' => 'reportes.partials.movimientos_boveda',
            'data' => compact('resultados')
        ];
    }

    private function reporteResumenFinanciero(Request $request)
    {
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');
        $localId = $request->input('local_id');

        // Ventas
        $ventasQuery = Venta::where('estado', '!=', '0');
        if ($desde)
            $ventasQuery->whereDate('fecha_emision', '>=', $desde);
        if ($hasta)
            $ventasQuery->whereDate('fecha_emision', '<=', $hasta);
        if ($localId)
            $ventasQuery->where('sucursal', $localId);

        $ventasTotal = $ventasQuery->sum('total');
        $ventasCantidad = $ventasQuery->count();

        // Gastos e Ingresos Extra (Operaciones de Caja)
        $operacionesQuery = \App\Models\OperacionCaja::query();
        if ($desde)
            $operacionesQuery->whereDate('created_at', '>=', $desde);
        if ($hasta)
            $operacionesQuery->whereDate('created_at', '<=', $hasta);
        if ($localId)
            $operacionesQuery->where('sucursal_id', $localId);

        $egresos = $operacionesQuery->clone()->whereIn('tipo', ['Egreso', 'egreso', 'sustraccion', 'gasto', 'retiro'])->sum('importe');
        $ingresosExtra = $operacionesQuery->clone()->whereIn('tipo', ['Ingreso', 'ingreso', 'aportacion', 'aporte'])->where('partida', '!=', 'Cobro Deuda')->sum('importe');

        // Compras
        $comprasQuery = Compra::query();
        if ($desde)
            $comprasQuery->whereDate('fecha_emision', '>=', $desde);
        if ($hasta)
            $comprasQuery->whereDate('fecha_emision', '<=', $hasta);
        if ($localId)
            $comprasQuery->where('local_destino', $localId);

        $comprasTotal = $comprasQuery->sum('total_pagar');
        $comprasCantidad = $comprasQuery->count();

        return [
            'view' => 'reportes.partials.resumen_financiero',
            'data' => compact('ventasTotal', 'ventasCantidad', 'egresos', 'ingresosExtra', 'comprasTotal', 'comprasCantidad', 'desde', 'hasta')
        ];
    }
}

