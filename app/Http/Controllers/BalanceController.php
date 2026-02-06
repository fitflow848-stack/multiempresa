<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AlmacenIngresoDetalle;
use App\Models\Deuda;
use App\Models\Compra;
use App\Models\CierreCaja;
use App\Models\Venta;
use App\Models\OperacionCaja;
use Illuminate\Support\Facades\DB;

class BalanceController extends Controller
{
    public function index(Request $request)
    {
        $fecha = $request->input('fecha', now()->format('Y-m-d'));

        // 1. ACTIVO CORRIENTE

        // CAJA: Dinero efectivo en cajas ABIERTAS
        // Se calcula: Monto Apertura + Ventas en Efectivo del turno + Ingresos Caja - Egresos Caja
        $cajasAbiertas = CierreCaja::whereNull('fecha_cierre')->get();
        $caja = 0.00;

        foreach ($cajasAbiertas as $box) {
            $caja += $box->monto_apertura;
            $caja += $box->aportaciones ?? 0;
            $caja -= $box->sustracciones ?? 0;

            // Ventas en efectivo asociadas a esta caja por fecha o ID
            // Asumimos que al abrir caja, las ventas se asocian a ese cierre_caja_id
            $ventasEfectivo = Venta::where('cierre_caja_id', $box->id)
                ->whereHas('tipoPago', function ($q) {
                    $q->where('es_efectivo', true);
                })
                ->where('estado', '!=', '0') // No anuladas
                ->sum('total');

            $caja += $ventasEfectivo;

            // Operaciones de caja (Ingresos/Gastos extras)
            $ingresosCaja = OperacionCaja::where('cierre_caja_id', $box->id)
                ->where('tipo', 'ingreso')
                ->sum('importe');

            $egresosCaja = OperacionCaja::where('cierre_caja_id', $box->id)
                ->where('tipo', 'egreso')
                ->sum('importe');

            $caja += $ingresosCaja;
            $caja -= $egresosCaja;
        }

        // BANCOS: Placeholder por ahora
        $bancos = 0.00;

        // INVENTARIO: Valorizado al costo promedio o costo de entrada
        // Consideramos solo stock positivo
        $inventario = AlmacenIngresoDetalle::where('cantidad', '>', 0)
            ->sum(DB::raw('cantidad * costo'));

        // CUENTAS POR COBRAR (CxC)
        // Deudas pendientes (estado 'pendiente' o 'parcial')
        $cxc = Deuda::whereIn('estado', [Deuda::ESTADO_PENDIENTE, Deuda::ESTADO_PARCIAL])
            ->whereDate('fecha_venta', '<=', $fecha)
            ->sum('monto_deuda');

        // Anticipos y otros (Placeholders)
        $anticipo_proveedores = 0.00;
        $adelantos_personal = 0.00;
        $otros_activos_corrientes = 0.00;

        $total_activo_corriente = $caja + $bancos + $inventario + $cxc + $anticipo_proveedores + $adelantos_personal + $otros_activos_corrientes;

        // 2. ACTIVO NO CORRIENTE
        $activo_fijo = 0.00;
        $intangibles = 0.00;
        $detracciones = 0.00;
        $otros_activos_no_corrientes = 0.00;

        $total_activo_no_corriente = $activo_fijo + $intangibles + $detracciones + $otros_activos_no_corrientes;

        $total_activo = $total_activo_corriente + $total_activo_no_corriente;

        // 3. PASIVO CORRIENTE

        // COMPRAS A CREDITO
        // Compras marcadas como credito que aun no estan pagadas.
        // Si no hay control de pagos de compras, asumimos todo lo que es credito suma.
        // Idealmente: Compra::where('credito', 1)->sum('total_pendiente')
        $compras_credito = Compra::where('credito', true)
            ->whereDate('fecha_emision', '<=', $fecha)
            ->sum('total_pagar');
        // TODO: Restar pagos realizados si existiera tabla de pagos a proveedores

        $adelanto_clientes = 0.00;
        $deuda_bancos = 0.00;
        $cxp_terceros = 0.00;
        $aporte = 0.00;
        $beneficio = 0.00;
        $impuestos_renta = 0.00;
        $otros_pasivos_corrientes = 0.00;

        $total_pasivo_corriente = $compras_credito + $adelanto_clientes + $deuda_bancos + $cxp_terceros + $aporte + $beneficio + $impuestos_renta + $otros_pasivos_corrientes;

        // 4. PASIVO NO CORRIENTE
        $otros_pasivos_no_corrientes = 0.00;
        $total_pasivo_no_corriente = $otros_pasivos_no_corrientes;

        $total_pasivo = $total_pasivo_corriente + $total_pasivo_no_corriente;

        // 5. PATRIMONIO
        // Activo - Pasivo = Patrimonio
        $patrimonio_calculado = $total_activo - $total_pasivo;

        return view('balance.index', compact(
            'fecha',
            'caja',
            'bancos',
            'inventario',
            'cxc',
            'anticipo_proveedores',
            'adelantos_personal',
            'otros_activos_corrientes',
            'total_activo_corriente',
            'activo_fijo',
            'intangibles',
            'detracciones',
            'otros_activos_no_corrientes',
            'total_activo_no_corriente',
            'total_activo',
            'compras_credito',
            'adelanto_clientes',
            'deuda_bancos',
            'cxp_terceros',
            'aporte',
            'beneficio',
            'impuestos_renta',
            'otros_pasivos_corrientes',
            'total_pasivo_corriente',
            'otros_pasivos_no_corrientes',
            'total_pasivo_no_corriente',
            'total_pasivo',
            'patrimonio_calculado'
        ));
    }
}
