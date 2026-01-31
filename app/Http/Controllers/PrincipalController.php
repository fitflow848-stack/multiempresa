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

class PrincipalController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $companyId = $user->company_id;

        // --- PEDIDOS VENTAS ---
        // Proformas (Cotizaciones)
        $proformas_pendientes_cnt = Cotizacion::where('company_id', $companyId)->count(); // Asumimos todas como pendientes si no hay estado
        $proformas_pendientes_monto = Cotizacion::where('company_id', $companyId)->sum('total');

        // Preventas y Reservas (Placeholders si no hay lógica definida)
        $preventas_pendientes_cnt = 0;
        $preventas_pendientes_monto = 0;
        $reservas_pendientes_cnt = 0;
        $reservas_pendientes_monto = 0;
        $reservas_entregar_cnt = 0;

        // --- VENTAS ---
        // Creditos pendientes (Podría ser ventas con saldo > 0 o simplemente usar Deuda)
        // Usaremos Deudas para ser consistentes con Tesoreria, o 0 si se distingue "Venta en proceso de credito"
        // Dejaremos placeholder para "Creditos pendientes" en sección VENTAS si es redundante con Tesoreria
        $creditos_pendientes_cnt = Deuda::where('sucursal_id', $companyId)->where('monto_deuda', '>', 0)->count();
        $creditos_pendientes_monto = Deuda::where('sucursal_id', $companyId)->where('monto_deuda', '>', 0)->sum('monto_deuda');

        // --- TESORERIA ---
        // Cobros (Clientes)
        $cobros_pendientes_cnt = $creditos_pendientes_cnt;
        $cobros_pendientes_monto = $creditos_pendientes_monto;

        $cobros_vencidos_cnt = Deuda::where('sucursal_id', $companyId)
            ->where('monto_deuda', '>', 0)
            ->where('fecha_vencimiento', '<', now())
            ->count();
        $cobros_vencidos_monto = Deuda::where('sucursal_id', $companyId)
            ->where('monto_deuda', '>', 0)
            ->where('fecha_vencimiento', '<', now())
            ->sum('monto_deuda');

        // Pagos (Proveedores) - Placeholder 0
        $pagos_pendientes_cnt = 0;
        $pagos_pendientes_monto = 0;
        $pagos_vencidos_cnt = 0;
        $pagos_vencidos_monto = 0;

        // --- COMPRAS ---
        // Comprobantes pendientes (Recibido = 0?)
        // Filtramos por proveedor->id_empresa o si Compra tiene id_empresa?
        // Asumiendo que Compra se relaciona a Company, intentamos query simple
        // Si no hay id_empresa en Compra, usar filtro por usuario->company_id (si usuario crea compra)
        $comprobantes_pendientes_cnt = Compra::whereHas('usuario', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })->where('recibido', 0)->count();

        $comprobantes_borrador_cnt = 0;

        // --- ALMACEN ---
        $productos_stock_cnt = Producto::where('id_empresa', $companyId)->where('cantidad', '>', 0)->count();
        $productos_sin_stock_cnt = Producto::where('id_empresa', $companyId)->where('cantidad', '<=', 0)->count();
        // $productos_stock_minimo_cnt = Producto::where('id_empresa', $companyId)->whereColumn('cantidad', '<=', 'stock_minimo')->count();
        $productos_stock_minimo_cnt = 0; // Placeholder

        // --- CAPITAL ACTUAL ---
        // Calculado desde AlmacenIngresoDetalle (Lotes activos)
        $stock_valuations = AlmacenIngresoDetalle::whereHas('producto', function ($q) use ($companyId) {
            $q->where('id_empresa', $companyId);
        })
            ->where('cantidad', '>', 0)
            ->select(
                DB::raw('SUM(cantidad * costo) as total_costo'),
                DB::raw('SUM(cantidad * pvp) as total_venta')
            )
            ->first();

        $capital_costo = $stock_valuations->total_costo ?? 0;
        $capital_venta = $stock_valuations->total_venta ?? 0;

        // Asumiendo PVP incluye IGV (18%)
        $capital_venta_neto = $capital_venta / 1.18;
        $capital_impuesto = $capital_venta - $capital_venta_neto;
        // Margen Bruto (Venta neta - Costo) o Margen Comercial (Venta - Costo)?
        // La imagen dice "Total Margen Utilidad". Usaremos (Venta - Costo).
        $capital_utilidad = $capital_venta - $capital_costo;
        $empresa = Company::where('id', $companyId)->first();
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
            'empresa'
        ));
    }
}
