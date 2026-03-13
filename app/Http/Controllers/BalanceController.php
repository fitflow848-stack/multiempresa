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
        $sucursal_id = $request->input('sucursal_id');

        // Asegurar que la empresa tenga los tipos por defecto para evitar errores de visualización y permitir registros
        \App\Helpers\AccountingHelper::ensureDefaults(auth()->user()->company_id);

        $baseData = $this->calculateData($fecha, $sucursal_id);
        $data = $this->refineData($baseData);
        $data['fecha'] = $fecha;
        $data['sucursales'] = \App\Models\Sucursal::where('company_id', auth()->user()->company_id)->activas()->get();
        $data['sucursal_id'] = $sucursal_id;
        return view('balance.index', $data);
    }

    public function export(Request $request)
    {
        $fecha = $request->input('fecha', now()->format('Y-m-d'));
        $sucursal_id = $request->input('sucursal_id');
        $baseData = $this->calculateData($fecha, $sucursal_id);
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
        $sucursal_id = $request->input('sucursal_id');

        // Asegurar valores por defecto
        \App\Helpers\AccountingHelper::ensureDefaults(auth()->user()->company_id);

        $baseData = $this->calculateData($fecha, $sucursal_id);
        $data = $this->refineData($baseData);
        $data['fecha'] = $fecha;
        $data['sucursales'] = \App\Models\Sucursal::where('company_id', auth()->user()->company_id)->activas()->get();
        $data['sucursal_id'] = $sucursal_id;
        return view('balance.graficos', $data);
    }

    private function refineData($data)
    {
        $tiposPasivos = $data['tiposPasivosCorrientes'];
        $tiposActivos = $data['tiposActivosCorrientes'];

        // 1. Mover TODOS los items del tipo "Adelantos Personal" que se registraron como Pasivos a Activos
        $indicesPersonal = $tiposPasivos->filter(function ($item) {
            $name = strtolower($item->nombre);
            return (str_contains($name, 'adelanto') && str_contains($name, 'personal')) || 
                   (str_contains($name, 'adelanto') && str_contains($name, 'trabajador'));
        });

        if ($indicesPersonal->isNotEmpty()) {
            $montoPersonalMoved = $indicesPersonal->sum('pasivos_sum_monto');
            
            // Eliminar de pasivos
            $data['tiposPasivosCorrientes'] = $tiposPasivos->reject(function ($item) use ($indicesPersonal) {
                return $indicesPersonal->pluck('id')->contains($item->id);
            });

            // Agregar a activos
            $existenteActivo = $tiposActivos->first(function ($item) {
                $name = strtolower($item->nombre);
                return str_contains($name, 'adelanto') && str_contains($name, 'personal');
            });

            if ($existenteActivo) {
                $existenteActivo->activos_sum_monto = ($existenteActivo->activos_sum_monto ?? 0) + $montoPersonalMoved;
            } else {
                $new = (object) [
                    'nombre' => 'Adelantos a Personal',
                    'activos_sum_monto' => $montoPersonalMoved
                ];
                $tiposActivos->push($new);
            }
        }

        // 2. Unificar "Adelanto Clientes" y "Adelanto de Clientes" en Pasivos
        // Usar la colección ya modificada $data['tiposPasivosCorrientes']
        $adelantoClientes = $data['tiposPasivosCorrientes']->filter(function ($item) {
            $name = strtolower($item->nombre);
            return str_contains($name, 'adelanto') && str_contains($name, 'cliente');
        });

        if ($adelantoClientes->count() > 1) {
            $totalAdelanto = $adelantoClientes->sum('pasivos_sum_monto');
            $keepId = $adelantoClientes->first()->id;

            // Mantener solo uno y sumar el resto
            $data['tiposPasivosCorrientes'] = $data['tiposPasivosCorrientes']->reject(function ($item) use ($adelantoClientes, $keepId) {
                return $adelantoClientes->pluck('id')->contains($item->id) && $item->id !== $keepId;
            });

            $finalObj = $data['tiposPasivosCorrientes']->firstWhere('id', $keepId);
            if ($finalObj) {
                $finalObj->nombre = 'Adelanto de Clientes';
                $finalObj->pasivos_sum_monto = $totalAdelanto;
            }
        }

        // Recalcular Totales
        $data['tiposActivosCorrientes'] = $tiposActivos->reject(function ($item) {
            return ($item->activos_sum_monto ?? 0) <= 0;
        });

        $data['total_activo_corriente'] = $data['caja'] + $data['inventario'] + $data['tiposActivosCorrientes']->sum('activos_sum_monto');
        $data['total_activo'] = $data['total_activo_corriente'] + $data['total_activo_no_corriente'];

        $data['tiposPasivosCorrientes'] = $data['tiposPasivosCorrientes']->reject(function ($item) {
            return ($item->pasivos_sum_monto ?? 0) <= 0;
        });

        $data['total_pasivo_corriente'] = $data['tiposPasivosCorrientes']->sum('pasivos_sum_monto');
        $data['total_pasivo'] = $data['total_pasivo_corriente'] + $data['total_pasivo_no_corriente'];

        // Patrimonio se calcula como Activo - Pasivo, ya que los aportes no están incluidos en Pasivos
        $data['patrimonio_calculado'] = $data['total_activo'] - $data['total_pasivo'];

        return $data;
    }

    private function calculateData($fecha, $sucursalId = null)
    {
        $user = Auth::user();

        // 1. ACTIVO CORRIENTE
        // CAJA: Dinero efectivo en todas las cajas de la empresa (abiertas y último cierre de las cerradas)
        $cajasQuery = Caja::withoutGlobalScopes()->where('company_id', $user->company_id);
        if ($sucursalId) {
            $cajasQuery->where('sucursal_id', $sucursalId);
        }
        $cajas = $cajasQuery->get();
        $caja = 0.00;

        foreach ($cajas as $cajaModel) {
            // Buscar el último cierre/sesión de esta caja hasta la fecha consultada
            $box = CierreCaja::withoutGlobalScopes()
                ->where('caja_id', $cajaModel->id)
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
                $ventasEfectivo = Venta::withoutGlobalScopes()
                    ->where('cierre_caja_id', $box->id)
                    ->whereDate('created_at', '<=', $fecha)
                    ->whereHas('tipoPago', function ($q) {
                        $q->where('es_efectivo', true);
                    })
                    ->where('estado', '!=', '0')
                    ->sum(DB::raw('monto_recibido - vuelto'));

                $subtotal += floatval($ventasEfectivo);

                // Operaciones de caja (Ingresos/Aportes) hasta la fecha
                $ingresosExtra = OperacionCaja::withoutGlobalScopes()
                    ->where('cierre_caja_id', $box->id)
                    ->whereDate('created_at', '<=', $fecha)
                    ->whereIn('tipo', ['ingreso', 'aportacion', 'aporte'])
                    ->where('es_efectivo', 1)
                    ->sum('importe');

                $subtotal += floatval($ingresosExtra);

                // Operaciones de caja (Egresos/Gastos/Sustracciones) hasta la fecha
                $egresosExtra = OperacionCaja::withoutGlobalScopes()
                    ->where('cierre_caja_id', $box->id)
                    ->whereIn('tipo', ['egreso', 'gasto', 'sustraccion', 'retiro'])
                    ->whereDate('created_at', '<=', $fecha)
                    ->where('es_efectivo', 1)
                    ->sum('importe');

                $subtotal -= floatval($egresosExtra);

                $caja += $subtotal;
            }
        }
        
        // APORTES FLOTANTES: Aportes que no entraron a la caja física (POS) pero son activos de la empresa
        // Esto permite que el balance cuadre sin afectar el arqueo del día.
        $aportesFlotantes = \App\Models\Pasivo::withoutGlobalScopes()
            ->where('company_id', $user->company_id)
            ->whereHas('tipo', function($q) {
                $q->where('nombre', 'Aporte');
            })
            ->whereNull('id_operacion_caja')
            ->whereDate('fecha_registro', '<=', $fecha)
            ->sum(DB::raw('monto - monto_pagado'));
            
        $caja += floatval($aportesFlotantes);

        // INVENTARIO: Valorizado al costo promedio o costo de entrada (Sistema)
        $inventario = AlmacenIngresoDetalle::withoutGlobalScopes()
            ->where('cantidad', '>', 0)
            ->whereHas('ingreso', function ($q) use ($user, $sucursalId) {
                $q->withoutGlobalScopes()->where('empresa_id', $user->company_id);
                if ($sucursalId) {
                    $q->where('sucursal_id', $sucursalId);
                }
            })
            ->sum(DB::raw('cantidad * costo'));

        // CUENTAS POR COBRAR: Monto de deuda pendiente de clientes
        $cxcQuery = Deuda::withoutGlobalScopes()
            ->where('company_id', $user->company_id)
            ->whereIn('estado', [Deuda::ESTADO_PENDIENTE, Deuda::ESTADO_PARCIAL])
            ->whereDate('fecha_venta', '<=', $fecha);

        if ($sucursalId) {
            $cxcQuery->where('sucursal_id', $sucursalId);
        }

        $cxc = $cxcQuery->sum(DB::raw('monto_deuda'));

        // ACTIVOS CORRIENTES (Desde el nuevo módulo)
        $tiposActivosCorrientes = \App\Models\TipoActivoCorriente::withoutGlobalScopes()->withSum([
            'activos' => function ($q) use ($user, $fecha, $sucursalId) {
                $q->withoutGlobalScopes()->where('company_id', $user->company_id)
                    ->where('is_settled', false) // Solo lo que no está saldado aún
                    ->whereDate('fecha_registro', '<=', $fecha);
                if ($sucursalId) {
                    $q->where('sucursal_id', $sucursalId);
                }
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
            $newType = (object)[
                'nombre' => 'Cuentas por Cobrar (CxC)',
                'activos_sum_monto' => $cxc
            ];
            $tiposActivosCorrientes->push($newType);
        }

        $total_activo_corriente = $caja + $inventario + $tiposActivosCorrientes->sum('activos_sum_monto');

        // 2. ACTIVO NO CORRIENTE
        $tiposActivosNoCorrientes = \App\Models\TipoActivo::withoutGlobalScopes()->withSum([
            'activos' => function ($q) use ($user, $fecha, $sucursalId) {
                $q->withoutGlobalScopes()->where('company_id', $user->company_id)
                    ->whereDate('fecha_adquisicion', '<=', $fecha);
                if ($sucursalId) {
                    $q->where('sucursal_id', $sucursalId);
                }
            }
        ], 'monto')->get();

        $total_activo_no_corriente = $tiposActivosNoCorrientes->sum('activos_sum_monto');
        $total_activo = $total_activo_corriente + $total_activo_no_corriente;

        // 3. PASIVO CORRIENTE
        $compras_credito_auto = 0; // Se desactiva la integración automática de Compras (Panel de Compras)

        // Obtener Pasivos Manuales
        $tiposPasivosCorrientes = \App\Models\TipoPasivo::withoutGlobalScopes()->withSum([
            'pasivos' => function ($q) use ($user, $fecha, $sucursalId) {
                $q->withoutGlobalScopes()
                    ->where('company_id', $user->company_id)
                    ->whereIn('estado', ['aprobado', 'pendiente', 'parcial'])
                    ->whereDate('fecha_registro', '<=', $fecha);
                if ($sucursalId) {
                    $q->where('sucursal_id', $sucursalId);
                }
            }
        ], 'monto')->withSum([
            'pasivos' => function ($q) use ($user, $fecha, $sucursalId) {
                $q->withoutGlobalScopes()
                    ->where('company_id', $user->company_id)
                    ->whereIn('estado', ['aprobado', 'pendiente', 'parcial'])
                    ->whereDate('fecha_registro', '<=', $fecha);
                if ($sucursalId) {
                    $q->where('sucursal_id', $sucursalId);
                }
            }
        ], 'monto_pagado')->get();

        // Calcular el neto de cada tipo
        foreach ($tiposPasivosCorrientes as $tipo) {
            $tipo->pasivos_sum_monto = ($tipo->pasivos_sum_monto ?? 0) - ($tipo->pasivos_sum_monto_pagado ?? 0);
        }

        // Patrimonio: Aportes (Calculado independientemente para incluir todos, incluso los pagados)
        $total_aportes = \App\Models\Pasivo::withoutGlobalScopes()
            ->where('company_id', $user->company_id)
            ->whereHas('tipo', function($q) {
                // Buscamos cualquier tipo que contenga "Aporte"
                $q->where('nombre', 'Aporte')
                  ->orWhere('nombre', 'like', 'Aportes%');
            })
            ->whereDate('fecha_registro', '<=', $fecha);

        if ($sucursalId) {
            $total_aportes->where('sucursal_id', $sucursalId);
        }

        $total_aportes = $total_aportes->sum('monto');

        // Remover el tipo "Aporte" de los Pasivos Corrientes para que no se duplique como deuda
        $tiposPasivosCorrientes = $tiposPasivosCorrientes->reject(function ($item) {
            $name = strtolower($item->nombre);
            return $name === 'aporte' || str_starts_with($name, 'aportes');
        });

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
