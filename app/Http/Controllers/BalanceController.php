<?php

namespace App\Http\Controllers;

use App\Exports\BalanceExport;
use Illuminate\Http\Request;
use App\Models\AlmacenIngresoDetalle;
use App\Models\Deuda;
use App\Models\Compra;
use App\Models\CierreCaja;
use App\Models\Venta;
use App\Models\OperacionCaja;
use App\Models\Caja;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Excel;

class BalanceController extends Controller
{
    public function index(Request $request)
    {
        $fecha = $request->input('fecha', now()->format('Y-m-d'));
        $baseData = $this->calculateData($fecha);
        $data = $this->refineData($baseData);
        $data['fecha'] = $fecha;
        return view('balance.index', $data);
    }

    public function export(Request $request)
    {
        $fecha = $request->input('fecha', now()->format('Y-m-d'));
        $baseData = $this->calculateData($fecha);
        $data = $this->refineData($baseData);

        $filename = "balance_general_{$fecha}.xlsx";

        return Excel::download(
            new BalanceExport($data, $fecha),
            $filename
        );
    }

    public function graficos(Request $request)
    {
        $fecha = $request->input('fecha', now()->format('Y-m-d'));
        $baseData = $this->calculateData($fecha);
        $data = $this->refineData($baseData);
        $data['fecha'] = $fecha;
        return view('balance.graficos', $data);
    }

    private function refineData($data)
    {
        $tiposPasivos = $data['tiposPasivosCorrientes'];
        $tiposActivos = $data['tiposActivosCorrientes'];

        // 1. Mover "Adelantos Personal" de Pasivos a Activos (es una cuenta por cobrar a empleados)
        $idxPersonal = $tiposPasivos->search(function ($item) {
            $name = strtolower($item->nombre);
            return str_contains($name, 'adelanto') && str_contains($name, 'personal');
        });

        if ($idxPersonal !== false) {
            $personalObj = $tiposPasivos->pull($idxPersonal);
            $monto = $personalObj->pasivos_sum_monto ?? 0;

            // Buscar si ya existe en activos
            $existente = $tiposActivos->first(function ($item) {
                $name = strtolower($item->nombre);
                return str_contains($name, 'adelanto') && str_contains($name, 'personal');
            });

            if ($existente) {
                $existente->activos_sum_monto = ($existente->activos_sum_monto ?? 0) + $monto;
            } else {
                $new = new \App\Models\TipoActivoCorriente();
                $new->nombre = 'Adelantos a Personal';
                $new->activos_sum_monto = $monto;
                $tiposActivos->push($new);
            }
        }

        // 2. Unificar "Adelanto Clientes" y "Adelanto de Clientes" en Pasivos
        $adelantoClientes = $tiposPasivos->filter(function ($item) {
            $name = strtolower($item->nombre);
            return str_contains($name, 'adelanto') && str_contains($name, 'cliente');
        });

        if ($adelantoClientes->count() > 1) {
            $totalAdelanto = $adelantoClientes->sum('pasivos_sum_monto');
            $keepId = $adelantoClientes->first()->id;

            // Mantener solo uno y sumar el resto
            $data['tiposPasivosCorrientes'] = $tiposPasivos->reject(function ($item) use ($adelantoClientes, $keepId) {
                return $adelantoClientes->pluck('id')->contains($item->id) && $item->id !== $keepId;
            });

            $finalObj = $data['tiposPasivosCorrientes']->firstWhere('id', $keepId);
            if ($finalObj) {
                $finalObj->nombre = 'Adelanto de Clientes';
                $finalObj->pasivos_sum_monto = $totalAdelanto;
            }
        }

        // Recalcular Totales
        $data['tiposActivosCorrientes'] = $tiposActivos;
        $data['total_activo_corriente'] = $data['caja'] + $data['inventario'] + $tiposActivos->sum('activos_sum_monto');
        $data['total_activo'] = $data['total_activo_corriente'] + $data['total_activo_no_corriente'];

        $data['total_pasivo_corriente'] = $data['tiposPasivosCorrientes']->sum('pasivos_sum_monto');
        $data['total_pasivo'] = $data['total_pasivo_corriente'] + $data['total_pasivo_no_corriente'];

        $data['patrimonio_calculado'] = $data['total_activo'] - $data['total_pasivo'];

        return $data;
    }

    private function calculateData($fecha)
    {
        $user = Auth::user();

        // 1. ACTIVO CORRIENTE
        // CAJA: Dinero efectivo en todas las cajas de la empresa (abiertas y último cierre de las cerradas)
        $cajas = Caja::where('company_id', $user->company_id)->get();
        $caja = 0.00;

        foreach ($cajas as $cajaModel) {
            // Buscar el último cierre/sesión de esta caja hasta la fecha consultada
            $box = CierreCaja::where('caja_id', $cajaModel->id)
                ->whereDate('created_at', '<=', $fecha)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$box) continue;

            // Si la caja estaba cerrada a la fecha consultada, usamos el monto_cierre.
            // Si estaba abierta (o se cerró después), calculamos el teórico a esa fecha.
            $estaCerradaAFecha = $box->fecha_cierre && $box->fecha_cierre->format('Y-m-d') <= $fecha;

            if ($estaCerradaAFecha) {
                $caja += floatval($box->monto_cierre);
            } else {
                // Cálculo dinámico para sesión que estaba activa a la fecha
                $subtotal = floatval($box->monto_apertura);

                // Ventas en efectivo asociadas a esta caja hasta la fecha
                $ventasEfectivo = Venta::where('cierre_caja_id', $box->id)
                    ->whereDate('created_at', '<=', $fecha)
                    ->whereHas('tipoPago', function ($q) {
                        $q->where('es_efectivo', true);
                    })
                    ->where('estado', '!=', '0')
                    ->sum(DB::raw('monto_recibido - vuelto'));

                $subtotal += floatval($ventasEfectivo);

                // Operaciones de caja (Ingresos/Aportes) hasta la fecha
                $ingresosExtra = OperacionCaja::where('cierre_caja_id', $box->id)
                    ->whereDate('created_at', '<=', $fecha)
                    ->whereIn('tipo', ['ingreso', 'aportacion', 'aporte'])
                    ->where('es_efectivo', 1)
                    ->sum('importe');

                $subtotal += floatval($ingresosExtra);

                // Operaciones de caja (Egresos/Gastos/Sustracciones) hasta la fecha
                $egresosExtra = OperacionCaja::where('cierre_caja_id', $box->id)
                    ->whereIn('tipo', ['egreso', 'gasto', 'sustraccion', 'retiro'])
                    ->whereDate('created_at', '<=', $fecha)
                    ->where('es_efectivo', 1)
                    ->sum('importe');

                $subtotal -= floatval($egresosExtra);
                
                $caja += $subtotal;
            }
        }

        // INVENTARIO: Valorizado al costo promedio o costo de entrada (Sistema)
        // Lo calculamos para toda la empresa para que coincida con el balance general
        $inventario = AlmacenIngresoDetalle::where('cantidad', '>', 0)
            ->whereHas('ingreso', function ($q) use ($user) {
                $q->where('empresa_id', $user->company_id);
            })
            ->sum(DB::raw('cantidad * costo'));

        // CUENTAS POR COBRAR: Monto de deuda pendiente de clientes de toda la empresa
        $cxc = Deuda::where('company_id', $user->company_id)
            ->whereIn('estado', [Deuda::ESTADO_PENDIENTE, Deuda::ESTADO_PARCIAL])
            ->whereDate('fecha_venta', '<=', $fecha)
            ->sum(DB::raw('monto_deuda')); // Usamos monto_deuda total

        // ACTIVOS CORRIENTES (Desde el nuevo módulo) - Toda la empresa
        $tiposActivosCorrientes = \App\Models\TipoActivoCorriente::withSum([
            'activos' => function ($q) use ($user, $fecha) {
                $q->where('company_id', $user->company_id)
                    ->whereDate('fecha_registro', '<=', $fecha);
            }
        ], 'monto')->get();

        // Integrar CxC Automático al tipo correspondiente
        $tipoCxC = $tiposActivosCorrientes->first(function ($item) {
            $lower = strtolower($item->nombre);
            return str_contains($lower, 'cuentas por cobrar') || str_contains($lower, 'cxc');
        });

        if ($tipoCxC) {
            $tipoCxC->activos_sum_monto = ($tipoCxC->activos_sum_monto ?? 0) + $cxc;
        } else if ($cxc > 0) {
            $newType = new \App\Models\TipoActivoCorriente();
            $newType->nombre = 'Cuentas por Cobrar (CxC)';
            $newType->activos_sum_monto = $cxc;
            $tiposActivosCorrientes->push($newType);
        }

        $total_activo_corriente = $caja + $inventario + $tiposActivosCorrientes->sum('activos_sum_monto');

        // 2. ACTIVO NO CORRIENTE - Toda la empresa
        $tiposActivosNoCorrientes = \App\Models\TipoActivo::withSum([
            'activos' => function ($q) use ($user, $fecha) {
                $q->where('company_id', $user->company_id)
                    ->whereDate('fecha_adquisicion', '<=', $fecha);
            }
        ], 'monto')->get();

        $total_activo_no_corriente = $tiposActivosNoCorrientes->sum('activos_sum_monto');
        $total_activo = $total_activo_corriente + $total_activo_no_corriente;

        // 3. PASIVO CORRIENTE
        $compras_credito_auto = 0;
        try {
            if (class_exists('App\Models\Compra')) {
                // Compras a crédito de toda la empresa
                $compras_credito_auto = Compra::where('company_id', $user->company_id)
                    ->where('credito', true)
                    ->whereDate('fecha_emision', '<=', $fecha)
                    ->sum(DB::raw('total_pagar - total_descuento')); // Ajustar cálculo si es necesario
            }
        } catch (\Exception $e) {
            $compras_credito_auto = 0;
        }

        // Obtener Pasivos Manuales de toda la empresa
        $tiposPasivosCorrientes = \App\Models\TipoPasivo::withSum([
            'pasivos' => function ($q) use ($user, $fecha) {
                $q->where('company_id', $user->company_id)
                    ->whereDate('fecha_registro', '<=', $fecha);
            }
        ], 'monto')->withSum([
            'pasivos' => function ($q) use ($user, $fecha) {
                $q->where('company_id', $user->company_id)
                    ->whereDate('fecha_registro', '<=', $fecha);
            }
        ], 'monto_pagado')->get();

        // Calcular el neto de cada tipo
        foreach ($tiposPasivosCorrientes as $tipo) {
            $tipo->pasivos_sum_monto = ($tipo->pasivos_sum_monto ?? 0) - ($tipo->pasivos_sum_monto_pagado ?? 0);
        }

        // Integrar Compras a crédito automáticas
        $tipoCC = $tiposPasivosCorrientes->first(function ($item) {
            return str_contains(strtolower($item->nombre), 'compras a cr');
        });

        if ($tipoCC) {
            $tipoCC->pasivos_sum_monto = ($tipoCC->pasivos_sum_monto ?? 0) + $compras_credito_auto;
        } else if ($compras_credito_auto > 0) {
            $newType = new \App\Models\TipoPasivo();
            $newType->nombre = 'Compras a crédito';
            $newType->pasivos_sum_monto = $compras_credito_auto;
            $tiposPasivosCorrientes->push($newType);
        }

        // Patrimonio: Aportes
        $tipoAporteObj = $tiposPasivosCorrientes->first(function ($item) {
            return strtolower($item->nombre) === 'aporte';
        });

        $total_aportes = 0;
        if ($tipoAporteObj) {
            $total_aportes = $tipoAporteObj->pasivos_sum_monto ?? 0;
            $tiposPasivosCorrientes = $tiposPasivosCorrientes->reject(function ($item) use ($tipoAporteObj) {
                return $item->id === $tipoAporteObj->id;
            });
        }

        $total_pasivo_corriente = $tiposPasivosCorrientes->sum('pasivos_sum_monto');

        // 4. PASIVO NO CORRIENTE
        $otros_pasivos_no_corrientes = 0.00;
        $total_pasivo_no_corriente = $otros_pasivos_no_corrientes;
        $total_pasivo = $total_pasivo_corriente + $total_pasivo_no_corriente;

        // 5. PATRIMONIO
        $patrimonio_calculado = $total_activo - $total_pasivo;

        return [
            'caja' => $caja,
            'inventario' => $inventario,
            'tiposActivosCorrientes' => $tiposActivosCorrientes,
            'total_activo_corriente' => $total_activo_corriente,
            'tiposActivosNoCorrientes' => $tiposActivosNoCorrientes,
            'total_activo_no_corriente' => $total_activo_no_corriente,
            'total_activo' => $total_activo,
            'tiposPasivosCorrientes' => $tiposPasivosCorrientes,
            'total_pasivo_corriente' => $total_pasivo_corriente,
            'otros_pasivos_no_corrientes' => $otros_pasivos_no_corrientes,
            'total_pasivo_no_corriente' => $total_pasivo_no_corriente,
            'total_pasivo' => $total_pasivo,
            'total_aportes' => $total_aportes,
            'patrimonio_calculado' => $patrimonio_calculado
        ];
    }
}
