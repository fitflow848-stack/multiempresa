<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Venta;
use App\Models\Compra;
use App\Models\Producto;
use App\Models\Cotizacion;
use App\Models\Deuda;
use App\Models\AlmacenIngresoDetalle;
use App\Models\Company;
use App\Models\CierreCaja;
use Carbon\Carbon;

class PrincipalController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $companyId = $user->company_id;
        $branchId = $user->branch_id;

        // --- PEDIDOS VENTAS ---
        $proformas_pendientes_cnt = Cotizacion::count();
        $proformas_pendientes_monto = Cotizacion::sum('total');

        $preventas_pendientes_cnt = 0;
        $preventas_pendientes_monto = 0;
        $reservas_pendientes_cnt = 0;
        $reservas_pendientes_monto = 0;
        $reservas_entregar_cnt = 0;

        // --- VENTAS ---
        $creditosQuery = Deuda::where('monto_deuda', '>', 0);
        if ($branchId) $creditosQuery->where('sucursal_id', $branchId);
        
        $creditos_pendientes_cnt = $creditosQuery->count();
        $creditos_pendientes_monto = $creditosQuery->sum('monto_deuda');

        // --- TESORERIA ---
        $cobros_pendientes_cnt = $creditos_pendientes_cnt;
        $cobros_pendientes_monto = $creditos_pendientes_monto;

        $cobrosVencidosQuery = Deuda::where('monto_deuda', '>', 0)
            ->where('fecha_vencimiento', '<', now());
        if ($branchId) $cobrosVencidosQuery->where('sucursal_id', $branchId);

        $cobros_vencidos_cnt = $cobrosVencidosQuery->count();
        $cobros_vencidos_monto = $cobrosVencidosQuery->sum('monto_deuda');

        $pagos_pendientes_cnt = 0;
        $pagos_pendientes_monto = 0;
        $pagos_vencidos_cnt = 0;
        $pagos_vencidos_monto = 0;

        // --- COMPRAS ---
        $comprobantes_pendientes_cnt = Compra::where('recibido', 0)->count();

        $comprobantes_borrador_cnt = 0;

        // --- ALMACEN ---
        $productos_stock_cnt = Producto::where('cantidad', '>', 0)->count();
        $productos_sin_stock_cnt = Producto::where('cantidad', '<=', 0)->count();

        // Stock counts should be branch-scoped if possible. 
        // For now, these are global product counts. 
        // If the user wants branch-specific stock counts, we'd need to join.

        // Productos en stock mínimo (cantidad <= stock_min)
        $branchId = $user->branch_id;

        // --- CONTEO DE PRODUCTOS (BRANCH SCOPED) ---
        // Obtenemos los IDs de productos que tienen stock positivo en la sucursal actual
        $inStockProductIdsQuery = AlmacenIngresoDetalle::whereHas('ingreso', function($q) use ($branchId) {
                if ($branchId) {
                    $q->where('sucursal_id', $branchId);
                }
            })
            ->where('cantidad', '>', 0)
            ->distinct()
            ->select('producto_id');

        $productos_stock_cnt = $inStockProductIdsQuery->get()->count();
        
        // Conteo de productos sin stock (productos de la empresa que no están en el query anterior)
        $productos_sin_stock_cnt = Producto::whereNotIn('id', $inStockProductIdsQuery)->count();

        $cajas_abiertas_cnt = CierreCaja::where('id_empresa', $user->company_id)
            ->whereNull('fecha_cierre')
            ->count();

        // --- CAPITAL ACTUAL ---
        $stock_valuations = AlmacenIngresoDetalle::whereHas('ingreso', function($q) use ($branchId) {
                if ($branchId) {
                    $q->where('sucursal_id', $branchId);
                }
            })
            ->where('cantidad', '>', 0)
            ->select(
                DB::raw('SUM(cantidad * costo) as total_costo'),
                DB::raw('SUM(cantidad * pvp) as total_venta')
            )
            ->first();

        $capital_costo = $stock_valuations->total_costo ?? 0;
        $capital_venta = $stock_valuations->total_venta ?? 0;

        // --- ALERTAS DE STOCK ---
        // Productos donde la SUMA de existencias en el lote es <= stock_min
        $stock_alerts = AlmacenIngresoDetalle::whereHas('ingreso', function($q) use ($branchId) {
                if ($branchId) {
                    $q->where('sucursal_id', $branchId);
                }
            })
            ->select('producto_id', DB::raw('SUM(cantidad) as total_existencias'), DB::raw('MAX(stock_min) as min_stock'))
            ->groupBy('producto_id')
            ->havingRaw('SUM(cantidad) <= MAX(stock_min)')
            ->where('cantidad', '>', 0) // Que al menos algo haya (o no)
            ->get();

        $productos_stock_minimo_cnt = $stock_alerts->count();

        $capital_venta_neto = $capital_venta / 1.18;
        $capital_impuesto = $capital_venta - $capital_venta_neto;
        $capital_utilidad = $capital_venta - $capital_costo;
        $empresa = Company::where('id', $companyId)->first();

        if (!$empresa) {
            abort(500, 'No se encontró la empresa asociada al usuario.');
        }

        // ====== DATOS PARA GRÁFICOS ======

        // Ventas de los últimos 6 meses
        $ventasMensualesQuery = Venta::where('id_empresa', $companyId)
            ->where('estado', '!=', 0)
            ->where('created_at', '>=', Carbon::now()->subMonths(6));
        
        if ($branchId) $ventasMensualesQuery->where('sucursal', $branchId);

        $ventasMensuales = $ventasMensualesQuery->select(
                DB::raw('MONTH(created_at) as mes'),
                DB::raw('YEAR(created_at) as anio'),
                DB::raw('SUM(total) as total'),
                DB::raw('COUNT(*) as cantidad')
            )
            ->groupBy('mes', 'anio')
            ->orderBy('anio')
            ->orderBy('mes')
            ->get();

        $mesesLabels = [];
        $ventasData = [];
        $cantidadVentas = [];

        for ($i = 5; $i >= 0; $i--) {
            $fecha = Carbon::now()->subMonths($i);
            $mes = $fecha->month;
            $anio = $fecha->year;
            $mesesLabels[] = $fecha->translatedFormat('M Y');

            $venta = $ventasMensuales->first(function ($v) use ($mes, $anio) {
                return $v->mes == $mes && $v->anio == $anio;
            });

            $ventasData[] = $venta ? round($venta->total, 2) : 0;
            $cantidadVentas[] = $venta ? $venta->cantidad : 0;
        }

        // Ventas por tipo de documento
        $ventasPorTipo = Venta::where('id_empresa', $companyId)
            ->where('estado', '!=', 0)
            ->where('created_at', '>=', Carbon::now()->subMonth())
            ->select('id_tido', DB::raw('SUM(total) as total'), DB::raw('COUNT(*) as cantidad'))
            ->groupBy('id_tido')
            ->get()
            ->map(function ($item) {
                $tipos = [1 => 'Factura', 2 => 'Boleta', 3 => 'N. Crédito', 4 => 'Ticket'];
                return [
                    'tipo' => $tipos[$item->id_tido] ?? 'Otro',
                    'total' => round($item->total, 2),
                    'cantidad' => $item->cantidad
                ];
            });

        // Top 5 productos más vendidos (últimos 30 días)
        $topProductosQuery = DB::table('venta_detalles')
            ->join('ventas', 'venta_detalles.id_venta', '=', 'ventas.id_venta')
            ->join('productos', 'venta_detalles.servicio_id', '=', 'productos.id')
            ->where('ventas.id_empresa', $companyId)
            ->where('ventas.estado', '!=', 0)
            ->where('ventas.created_at', '>=', Carbon::now()->subDays(30));
        
        if ($branchId) $topProductosQuery->where('ventas.sucursal', $branchId);

        $topProductos = $topProductosQuery->select(
                'productos.nombre',
                DB::raw('SUM(venta_detalles.cantidad) as cantidad'),
                DB::raw('SUM(venta_detalles.importe) as total')
            )
            ->groupBy('productos.id', 'productos.nombre')
            ->orderByDesc('cantidad')
            ->limit(5)
            ->get();

        // Ventas de los últimos 7 días
        $ventasSemana = Venta::where('id_empresa', $companyId)
            ->where('estado', '!=', 0)
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->select(
                DB::raw('DATE(created_at) as fecha'),
                DB::raw('SUM(total) as total')
            )
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        $diasLabels = [];
        $ventasDiarias = [];
        for ($i = 6; $i >= 0; $i--) {
            $fecha = Carbon::now()->subDays($i);
            $diasLabels[] = $fecha->translatedFormat('D d');
            $venta = $ventasSemana->first(fn($v) => $v->fecha == $fecha->toDateString());
            $ventasDiarias[] = $venta ? round($venta->total, 2) : 0;
        }

        // Venta de hoy
        $ventaHoyQuery = Venta::where('id_empresa', $companyId)
            ->where('estado', '!=', 0)
            ->whereDate('created_at', Carbon::today());
        if ($branchId) $ventaHoyQuery->where('sucursal', $branchId);
        $ventaHoy = $ventaHoyQuery->sum('total');

        // Venta de ayer para comparación
        $ventaAyerQuery = Venta::where('id_empresa', $companyId)
            ->where('estado', '!=', 0)
            ->whereDate('created_at', Carbon::yesterday());
        if ($branchId) $ventaAyerQuery->where('sucursal', $branchId);
        $ventaAyer = $ventaAyerQuery->sum('total');

        // Venta del mes
        $ventaMesQuery = Venta::where('id_empresa', $companyId)
            ->where('estado', '!=', 0)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year);
        if ($branchId) $ventaMesQuery->where('sucursal', $branchId);
        $ventaMes = $ventaMesQuery->sum('total');

        $chartData = [
            'mesesLabels' => $mesesLabels,
            'ventasData' => $ventasData,
            'cantidadVentas' => $cantidadVentas,
            'ventasPorTipo' => $ventasPorTipo,
            'topProductos' => $topProductos,
            'diasLabels' => $diasLabels,
            'ventasDiarias' => $ventasDiarias,
            'ventaHoy' => round($ventaHoy, 2),
            'ventaAyer' => round($ventaAyer, 2),
            'ventaMes' => round($ventaMes, 2),
        ];

        return view('welcome', compact(
            'proformas_pendientes_cnt',
            'proformas_pendientes_monto',
            'preventas_pendientes_cnt',
            'preventas_pendientes_monto',
            'reservas_pendientes_cnt',
            'reservas_pendientes_monto',
            'reservas_entregar_cnt',
            'creditos_pendientes_cnt',
            'creditos_pendientes_monto',
            'pagos_pendientes_cnt',
            'pagos_pendientes_monto',
            'pagos_vencidos_cnt',
            'pagos_vencidos_monto',
            'cobros_pendientes_cnt',
            'cobros_pendientes_monto',
            'cobros_vencidos_cnt',
            'cobros_vencidos_monto',
            'comprobantes_pendientes_cnt',
            'comprobantes_borrador_cnt',
            'productos_stock_cnt',
            'productos_sin_stock_cnt',
            'productos_stock_minimo_cnt',
            'capital_costo',
            'capital_venta',
            'capital_utilidad',
            'capital_impuesto',
            'empresa',
            'chartData'
        ));
    }
}
