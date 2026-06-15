<?php

namespace App\Http\Controllers;

use App\Exports\BalanceExport;
use App\Helpers\AccountingHelper;
use Illuminate\Http\Request;
use App\Models\AlmacenIngresoDetalle;
use App\Models\Deuda;
use App\Models\Compra;
use App\Models\CierreCaja;
use App\Models\Venta;
use App\Models\OperacionCaja;
use App\Models\Caja;
use App\Models\CuentaBancaria;
use App\Models\Pasivo;
use App\Models\Sucursal;
use App\Models\TipoActivo;
use App\Models\TipoActivoCorriente;
use App\Models\ActivoCorriente;
use App\Models\TipoPasivo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class BalanceController extends Controller
{
    public function index(Request $request)
    {
        $fecha = $request->input('fecha', now()->format('Y-m-d'));
        $sucursal_id = $request->input('sucursal_id');

        // Asegurar que la empresa tenga los tipos por defecto para evitar errores de visualización y permitir registros
        AccountingHelper::ensureDefaults(auth()->user()->company_id);

        $baseData = $this->calculateData($fecha, $sucursal_id);
        $data = $this->refineData($baseData);
        $data['fecha'] = $fecha;
        $data['sucursales'] = Sucursal::where('company_id', auth()->user()->company_id)->activas()->get();
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
        AccountingHelper::ensureDefaults(auth()->user()->company_id);

        $baseData = $this->calculateData($fecha, $sucursal_id);
        $data = $this->refineData($baseData);
        $data['fecha'] = $fecha;
        $data['sucursales'] = Sucursal::where('company_id', auth()->user()->company_id)->activas()->get();
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

        $data['total_activo_corriente'] = $data['caja'] + ($data['bancos'] ?? 0) + $data['inventario'] + $data['tiposActivosCorrientes']->sum('activos_sum_monto');
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

        // CAJA: Usar el mismo cálculo que el cierre de caja (teórico) para consistencia
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
            $estaCerradaAFecha = $box->fecha_cierre && $box->fecha_cierre->format('Y-m-d') <= $fecha;

            if ($estaCerradaAFecha) {
                $caja += floatval($box->monto_cierre);
            } else {
                // Usar calcularTotalesDinamicos para consistencia con el cierre de caja
                $totales = $box->calcularTotalesDinamicos();
                $subtotal = floatval($box->monto_apertura)
                    + floatval($totales['ingresos_efectivo'])
                    + floatval($totales['aportaciones_efectivo'])
                    - floatval($totales['egresos_efectivo'])
                    - floatval($totales['sustracciones_efectivo']);

                $caja += $subtotal;
            }
        }
        
        // APORTES FLOTANTES: Aportes que no entraron a la caja física (POS) pero son activos de la empresa
        // Esto permite que el balance cuadre sin afectar el arqueo del día.
        $aportesFlotantesQuery = Pasivo::withoutGlobalScopes()
            ->where('company_id', $user->company_id)
            ->whereHas('tipo', function($q) {
                $q->where('nombre', 'Aporte')
                  ->orWhere('nombre', 'like', 'Aportes%');
            })
            ->whereNull('id_operacion_caja')
            ->whereDate('fecha_registro', '<=', $fecha);

        if ($sucursalId) {
            $aportesFlotantesQuery->where('sucursal_id', $sucursalId);
        }

        $aportesFlotantes = $aportesFlotantesQuery->sum(DB::raw('monto - monto_pagado'));
            
        $caja += floatval($aportesFlotantes);

        // BANCOS: Saldo de cuentas bancarias activas
        $bancosQuery = CuentaBancaria::withoutGlobalScopes()
            ->where('company_id', $user->company_id)
            ->where('is_active', true);

        if ($sucursalId) {
            $bancosQuery->where('sucursal_id', $sucursalId);
        }

        $bancos = $bancosQuery->sum('saldo_actual');

        $inventario = DB::table('almacen_ingreso_detalle as d')
            ->join('almacen_ingresos as i', 'i.id', '=', 'd.ingreso_id')
            ->where('i.empresa_id', $user->company_id)
            ->when($sucursalId, fn($q) => $q->where('i.sucursal_id', $sucursalId))
            ->where(function ($q) {
                $q->whereNull('i.observacion')
                  ->orWhere('i.observacion', 'NOT LIKE', '[AJUSTE]%')
                  ->orWhere('d.cantidad', '>=', 0);
            })
            ->select('d.producto_id', 'd.producto_linea_id', DB::raw('SUM(d.cantidad * d.costo) as valor'))
            ->groupBy('d.producto_id', 'd.producto_linea_id')
            ->havingRaw('SUM(d.cantidad) > 0')
            ->get()
            ->sum('valor');

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
        // Sumar solo el monto PENDIENTE (monto - monto_cobrado) de activos no saldados
        $tiposActivosCorrientes = TipoActivoCorriente::withoutGlobalScopes()
            ->where('company_id', $user->company_id)
            ->get()
            ->map(function ($tipo) use ($user, $fecha, $sucursalId) {
                $query = ActivoCorriente::withoutGlobalScopes()
                    ->where('company_id', $user->company_id)
                    ->where('tipo_activo_corriente_id', $tipo->id)
                    ->where('is_settled', false)
                    ->whereDate('fecha_registro', '<=', $fecha);
                if ($sucursalId) {
                    $query->where('sucursal_id', $sucursalId);
                }
                $tipo->activos_sum_monto = $query->selectRaw('COALESCE(SUM(monto - COALESCE(monto_cobrado, 0)), 0) as total')->value('total');
                return $tipo;
            });

        // Integrar CxC Automático al tipo correspondiente
        // NOTA: Si ya existe un tipo "Cuentas por Cobrar (POS)" en activos corrientes,
        // esos registros ya representan las deudas del POS, no duplicar con la tabla deudas.
        $tipoCxC = $tiposActivosCorrientes->first(function ($item) {
            $lower = strtolower($item->nombre);
            return str_contains($lower, 'cuentas por cobrar') || str_contains($lower, 'cxc');
        });

        if ($tipoCxC) {
            // Ya existe el tipo con montos de activos_corrientes, usar solo el monto de deudas
            // (que es la fuente real) y reemplazar el monto del activo corriente
            $tipoCxC->activos_sum_monto = $cxc;
        } else if ($cxc > 0) {
            $newType = (object)[
                'nombre' => 'Cuentas por Cobrar (CxC)',
                'activos_sum_monto' => $cxc
            ];
            $tiposActivosCorrientes->push($newType);
        }

        $total_activo_corriente = $caja + $bancos + $inventario + $tiposActivosCorrientes->sum('activos_sum_monto');

        // 2. ACTIVO NO CORRIENTE
        $tiposActivosNoCorrientes = TipoActivo::withoutGlobalScopes()
            ->where('company_id', $user->company_id)
            ->withSum([
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
        $tiposPasivosCorrientes = TipoPasivo::withoutGlobalScopes()
            ->where('company_id', $user->company_id)
            ->withSum([
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

        // Patrimonio: Aportes (desde la tabla aportes - módulo principal de aportes)
        $total_aportes_query = \App\Models\Aporte::withoutGlobalScopes()
            ->where('company_id', $user->company_id)
            ->whereDate('fecha_registro', '<=', $fecha);

        $total_aportes_modulo = $total_aportes_query->sum('monto');

        // También incluir aportes registrados en pasivos (sistema legacy)
        $total_aportes_pasivos = Pasivo::withoutGlobalScopes()
            ->where('company_id', $user->company_id)
            ->whereHas('tipo', function($q) {
                $q->where('nombre', 'Aporte')
                  ->orWhere('nombre', 'like', 'Aportes%');
            })
            ->whereDate('fecha_registro', '<=', $fecha);

        if ($sucursalId) {
            $total_aportes_pasivos->where('sucursal_id', $sucursalId);
        }

        $total_aportes_pasivos = $total_aportes_pasivos->sum(DB::raw('monto - monto_pagado'));

        $total_aportes = $total_aportes_modulo + $total_aportes_pasivos;

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
            'bancos' => $bancos,
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
