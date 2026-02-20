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
        $locales = Sucursal::all(); // Assuming Sucursal tracks locals, or Company if single tenant

        // Types of reports key-value fetch from DB
        $reports = DB::table('reports')->where('active', true)->orderBy('id')->get();
        // Group by category to match previous structure
        $reportTypes = [];
        foreach ($reports as $r) {
            $reportTypes[$r->category][$r->id] = $r->name;
        }

        return view('reportes.index', compact('familias', 'vendedores', 'locales', 'reportTypes'));
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
        $query = VentaDetalle::with(['venta.user', 'producto', 'almacenIngresoDetalle'])
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
        // Usamos el nombre del producto porque diferentes lotes pueden tener diferentes servicio_id
        $agrupados = $detalles->groupBy(function ($item) {
            $nombreProducto = $item->producto->nombre ?? $item->nombre_servicio ?? 'Sin nombre';
            return $item->id_venta . '|||' . $nombreProducto;
        })->map(function ($grupo) {
            $primero = $grupo->first();
            $cantidadTotal = $grupo->sum('cantidad');

            // Calcular el costo promedio ponderado de todos los items del grupo
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
            $subtotalVenta = $grupo->sum('importe');
            $ganancia = $subtotalVenta - $costoTotalGrupo;

            return (object) [
                'venta' => $primero->venta,
                'producto' => $primero->producto,
                'cantidad' => $cantidadTotal,
                'precio_unitario' => $primero->precio_unitario,
                'costo_unitario' => $costoUnitario,
                'subtotal' => $subtotalVenta,
                'costo_total' => $costoTotalGrupo,
                'ganancia' => $ganancia,
            ];
        })->values();

        // Calcular totales
        $totales = (object) [
            'cantidad' => $agrupados->sum('cantidad'),
            'subtotal' => $agrupados->sum('subtotal'),
            'costo_total' => $agrupados->sum('costo_total'),
            'ganancia' => $agrupados->sum('ganancia'),
        ];

        $resultados = $agrupados;

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
        $query = Venta::with(['cliente', 'user', 'tipoPago'])
            ->where('estado', '!=', '0')
            ->orderByDesc('fecha_emision');

        if ($request->input('desde'))
            $query->whereDate('fecha_emision', '>=', $request->input('desde'));
        if ($request->input('hasta'))
            $query->whereDate('fecha_emision', '<=', $request->input('hasta'));
        if ($request->input('vendedor_id'))
            $query->where('id_usuario', $request->input('vendedor_id'));
        if ($request->input('tipo_comprobante')) {
            // Logic to filter by doc type if needed, utilizing the accessor or specific series
        }

        $resultados = $query->limit(200)->get();
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
        $query = Deuda::with(['cliente', 'venta'])
            ->where('monto_deuda', '>', 0)
            ->whereNotIn('estado', ['pagada', 'anulada'])
            ->orderByDesc('fecha_venta');

        if ($request->input('desde'))
            $query->whereDate('fecha_venta', '>=', $request->input('desde'));
        if ($request->input('hasta'))
            $query->whereDate('fecha_venta', '<=', $request->input('hasta'));

        $resultados = $query->limit(200)->get();
        return [
            'view' => 'reportes.partials.por_cobrar',
            'data' => compact('resultados')
        ];
    }

    private function reportePorCobrarConsolidado(Request $request)
    {
        // Deudas pendientes agrupadas por cliente
        $query = Deuda::select(
            'cliente_id',
            DB::raw('count(*) as total_documentos'),
            DB::raw('sum(monto_deuda) as total_deuda'),
            DB::raw('min(fecha_vencimiento) as vencimiento_mas_antiguo')
        )
            ->where('monto_deuda', '>', 0)
            ->whereNotIn('estado', ['pagada', 'anulada'])
            ->groupBy('cliente_id')
            ->orderByDesc('total_deuda');

        if ($request->input('vendedor_id')) {
            $query->where('user_id', $request->input('vendedor_id'));
        }

        $resultados = $query->with('cliente')->limit(200)->get();
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
        $query = Compra::with(['proveedor', 'usuario'])
            ->orderByDesc('fecha_emision');

        if ($request->input('desde'))
            $query->whereDate('fecha_emision', '>=', $request->input('desde'));
        if ($request->input('hasta'))
            $query->whereDate('fecha_emision', '<=', $request->input('hasta'));

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
        $query = AlmacenIngresoDetalle::where('cantidad', '>', 0)
            ->join('almacen_ingresos', 'almacen_ingreso_detalle.ingreso_id', '=', 'almacen_ingresos.id')
            ->select(
                DB::raw('SUM(cantidad * costo) as total_capital')
            );

        $total = $query->first()->total_capital ?? 0;

        $detalles = AlmacenIngresoDetalle::where('almacen_ingreso_detalle.cantidad', '>', 0)
            ->with(['producto.marca', 'producto.familia', 'producto.laboratorio'])
            ->select(
                'producto_id',
                DB::raw('SUM(cantidad) as stock'),
                DB::raw('SUM(cantidad * costo) as valor'),
                DB::raw('AVG(costo) as costo_unitario_promedio')
            )
            ->groupBy('producto_id')
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
        $query = AlmacenIngresoDetalle::where('cantidad', '>', 0)
            ->select(
                DB::raw('SUM(cantidad * costo) as total_capital')
            );

        $total = $query->first()->total_capital ?? 0;

        $detalles = AlmacenIngresoDetalle::where('cantidad', '>', 0)
            ->with('producto')
            ->select('producto_id', DB::raw('SUM(cantidad) as stock'), DB::raw('SUM(cantidad * costo) as valor'))
            ->groupBy('producto_id')
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
        $resultados = AlmacenIngresoDetalle::where('cantidad', '>', 0)
            ->whereColumn('costo', '>', 'pvp')
            ->with(['producto', 'ingreso'])
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
        $query = AlmacenIngresoDetalle::where('cantidad', '>', 0)
            ->select('producto_id', DB::raw('SUM(cantidad) as stock_total'))
            ->groupBy('producto_id')
            ->orderByDesc('stock_total')
            ->with('producto');

        $resultados = $query->limit(200)->get();
        return [
            'view' => 'reportes.partials.stock_consolidado',
            'data' => compact('resultados')
        ];
    }

    private function reporteStockPorLocal(Request $request)
    {
        $resultados = AlmacenIngresoDetalle::where('cantidad', '>', 0)
            ->select('producto_id', DB::raw('SUM(cantidad) as stock_total'))
            ->groupBy('producto_id')
            ->with('producto')
            ->limit(100)->get();

        return [
            'view' => 'reportes.partials.stock_local',
            'data' => compact('resultados')
        ];
    }

    private function reporteStockPorReferencia(Request $request)
    {
        $resultados = AlmacenIngresoDetalle::where('cantidad', '>', 0)
            ->with('producto')
            ->orderBy('producto_id')
            ->orderBy('lote')
            ->limit(200)
            ->get();

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
        // Productos próximos a vencer (3 meses por defecto)
        $meses = $request->input('meses', 3);
        $fechaLimite = now()->addMonths($meses);

        $query = AlmacenIngresoDetalle::with(['producto.familia', 'ingreso'])
            ->where('cantidad', '>', 0)
            ->whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento', '<=', $fechaLimite)
            ->orderBy('fecha_vencimiento', 'asc');

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
            ->where('v.estado', '!=', '0');

        $queryOperaciones = DB::table('operaciones_caja as o')
            ->select(
                'o.created_at as fecha',
                'o.partida as operacion',
                'o.tipo as tipo',
                DB::raw("o.tipo as detalle"),
                'o.concepto',
                DB::raw("'Efectivo' as metodo_pago"),
                'o.importe',
                'u.name as usuario'
            )
            ->join('users as u', 'u.id', '=', 'o.user_id');

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
}
