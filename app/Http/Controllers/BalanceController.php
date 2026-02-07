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
        $cajasAbiertas = CierreCaja::whereNull('fecha_cierre')->get();
        $caja = 0.00;

        foreach ($cajasAbiertas as $box) {
            $caja += $box->monto_apertura;
            $caja += $box->aportaciones ?? 0;
            $caja -= $box->sustracciones ?? 0;

            // Ventas en efectivo asociadas a esta caja por fecha o ID
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

        // INVENTARIO: Valorizado al costo promedio o costo de entrada (Sistema)
        $inventario = AlmacenIngresoDetalle::where('cantidad', '>', 0)
            ->sum(DB::raw('cantidad * costo'));

        // CUENTAS POR COBRAR Calculado (Sistema)
        // Deudas pendientes (estado 'pendiente' o 'parcial')
        $cxc_auto = Deuda::whereIn('estado', [Deuda::ESTADO_PENDIENTE, Deuda::ESTADO_PARCIAL])
            ->whereDate('fecha_venta', '<=', $fecha)
            ->sum('monto_deuda');

        // ACTIVOS CORRIENTES (Desde el nuevo módulo: Bancos, CxC Extras, Anticipos, Otros)
        $tiposActivosCorrientes = \App\Models\TipoActivoCorriente::withSum(['activos' => function ($q) use ($fecha) {
            $q->whereDate('fecha_registro', '<=', $fecha);
        }], 'monto')->get();

        // Integrar CxC Automático al tipo correspondiente
        $tipoCxC = $tiposActivosCorrientes->first(function ($item) {
            return \Illuminate\Support\Str::contains(strtolower($item->nombre), 'cuentas por cobrar') ||
                \Illuminate\Support\Str::contains(strtolower($item->nombre), 'cxc');
        });

        if ($tipoCxC) {
            $tipoCxC->activos_sum_monto = ($tipoCxC->activos_sum_monto ?? 0) + $cxc_auto;
        } else if ($cxc_auto > 0) {
            $newType = new \App\Models\TipoActivoCorriente();
            $newType->nombre = 'Cuentas por Cobrar (Sistema)';
            $newType->activos_sum_monto = $cxc_auto;
            $tiposActivosCorrientes->push($newType);
        }

        // Total Activo Corriente = Caja + Inventario + (Bancos + CxC + Otros del módulo)
        $total_manual_y_cxc = $tiposActivosCorrientes->sum('activos_sum_monto');
        $total_activo_corriente = $caja + $inventario + $total_manual_y_cxc;

        // 2. ACTIVO NO CORRIENTE
        $tiposActivosNoCorrientes = \App\Models\TipoActivo::withSum(['activos' => function ($q) use ($fecha) {
            $q->whereDate('fecha_adquisicion', '<=', $fecha);
        }], 'monto')->get();

        $total_activo_no_corriente = $tiposActivosNoCorrientes->sum('activos_sum_monto');

        $total_activo = $total_activo_corriente + $total_activo_no_corriente;

        // 3. PASIVO CORRIENTE

        // Calculo AUTOMATICO de Compras a Credito
        $compras_credito_auto = 0;
        try {
            if (class_exists('App\Models\Compra')) {
                $compras_credito_auto = Compra::where('credito', true)
                    ->whereDate('fecha_emision', '<=', $fecha)
                    ->sum('total_pagar');
            }
        } catch (\Exception $e) {
            $compras_credito_auto = 0;
        }

        // Obtener Pasivos Manuales desde el nuevo módulo
        $tiposPasivosCorrientes = \App\Models\TipoPasivo::withSum(['pasivos' => function ($q) use ($fecha) {
            $q->whereDate('fecha_registro', '<=', $fecha);
        }], 'monto')->get();

        // Integrar el cálculo automático a la categoría correspondiente (Compras a crédito)
        $tipoCC = $tiposPasivosCorrientes->first(function ($item) {
            return \Illuminate\Support\Str::contains(strtolower($item->nombre), 'compras a cr');
        });

        if ($tipoCC) {
            $tipoCC->pasivos_sum_monto = ($tipoCC->pasivos_sum_monto ?? 0) + $compras_credito_auto;
        } else if ($compras_credito_auto > 0) {
            $newType = new \App\Models\TipoPasivo();
            $newType->nombre = 'Compras a crédito (Sistema)';
            $newType->pasivos_sum_monto = $compras_credito_auto;
            $tiposPasivosCorrientes->push($newType);
        }

        $total_pasivo_corriente = $tiposPasivosCorrientes->sum('pasivos_sum_monto');

        // 4. PASIVO NO CORRIENTE
        $otros_pasivos_no_corrientes = 0.00;
        $total_pasivo_no_corriente = $otros_pasivos_no_corrientes;

        $total_pasivo = $total_pasivo_corriente + $total_pasivo_no_corriente;

        // 5. PATRIMONIO
        $patrimonio_calculado = $total_activo - $total_pasivo;

        return view('balance.index', compact(
            'fecha',
            'caja',
            'inventario',
            'tiposActivosCorrientes',
            'total_activo_corriente',
            'tiposActivosNoCorrientes',
            'total_activo_no_corriente',
            'total_activo',
            'tiposPasivosCorrientes',
            'total_pasivo_corriente',
            'otros_pasivos_no_corrientes',
            'total_pasivo_no_corriente',
            'total_pasivo',
            'patrimonio_calculado'
        ));
    }
}
