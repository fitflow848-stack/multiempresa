<?php

namespace App\Http\Controllers;

use App\Models\CierreCaja;
use App\Models\Venta;
use App\Models\Caja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CierreCajaController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $selectedCajaId = session('selected_caja_id');

        $query = CierreCaja::with(['user', 'caja'])->latest();

        // Filtro por empresa
        $query->where('id_empresa', $user->company_id);

        // Filtro por la caja seleccionada en la sesión (Solo para usuarios sin rol administrativo)
        if ($selectedCajaId && !$user->hasAnyRole(['super_admin', 'admin_empresa', 'supervisor'])) {
            $query->where('caja_id', $selectedCajaId);
        }

        // Si no es admin/supervisor, solo ve sus propios cierres
        if (!$user->hasAnyRole(['super_admin', 'admin_empresa', 'supervisor'])) {
            $query->where('user_id', $user->id);
        }

        // Aplicar filtros
        if ($request->filled('user_id') && $user->hasAnyRole(['super_admin', 'admin_empresa', 'supervisor'])) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        if ($request->filled('caja_id') && $user->hasAnyRole(['super_admin', 'admin_empresa', 'supervisor'])) {
            $query->where('caja_id', $request->caja_id);
        }

        $cierres = $query->paginate(20);

        // Corregir lógica de openCaja para que coincida con lo que busca el POS y VentaService
        $openCaja = null;
        if ($selectedCajaId) {
            $openCaja = CierreCaja::where('caja_id', $selectedCajaId)
                ->whereNull('fecha_cierre')
                ->latest()
                ->first();
        } else {
            // Si no hay caja seleccionada, ver si tiene alguna abierta en general
            $openCaja = CierreCaja::where('user_id', $user->id)
                ->whereNull('fecha_cierre')
                ->latest()
                ->first();
        }

        return view('cierres.index', compact('cierres', 'openCaja'));
    }

    public function create()
    {
        $selectedCajaId = session('selected_caja_id');

        if (!$selectedCajaId) {
            return redirect()->route('cierre-caja.index')->with('error', 'Debe seleccionar una caja activa en el menú superior antes de abrir una sesión.');
        }

        // Verificar si la caja ya está abierta
        $openCaja = CierreCaja::where('caja_id', $selectedCajaId)
            ->whereNull('fecha_cierre')
            ->first();

        if ($openCaja) {
            // Si el usuario actual es quien la tiene abierta, redirigir al detalle
            if ($openCaja->user_id == Auth::id()) {
                return redirect()->route('cierre-caja.show', $openCaja->id)
                    ->with('info', 'Ya tienes una sesión abierta para esta caja.');
            }

            // Si es otro usuario
            $msg = "La caja '" . $openCaja->caja->nombre . "' ya está siendo utilizada por el usuario " . $openCaja->user->name . ". Debe esperar a que cierre su sesión.";
            return redirect()->route('cierre-caja.index')->with('error', $msg);
        }

        // Obtener el último cierre de la caja seleccionada
        $ultimoCierre = CierreCaja::where('caja_id', $selectedCajaId)
            ->whereNotNull('fecha_cierre')
            ->orderBy('created_at', 'desc')
            ->first();

        // El saldo inicial será el monto de cierre del último arqueo, o 0 si no hay cierres previos
        $saldoInicial = $ultimoCierre ? $ultimoCierre->monto_cierre : 0.00;

        return view('cierres.create', compact('saldoInicial', 'ultimoCierre'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fecha_cierre' => 'nullable|date',
            'monto_apertura' => 'required|numeric',
            'monto_cierre' => 'required|numeric',
            'ingresos' => 'nullable|numeric',
            'egresos' => 'nullable|numeric',
            'aportaciones' => 'nullable|numeric', // Nuevo campo
            'sustracciones' => 'nullable|numeric', // Nuevo campo
            'observaciones' => 'nullable|string',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $data['user_id'] = $user->id;
        $data['id_empresa'] = $user->company_id;
        $data['caja_id'] = session('selected_caja_id');

        if (!$data['caja_id']) {
            return back()->with('error', 'Error: No hay una caja activa seleccionada.');
        }

        // Validación final de seguridad: Solo una sesión abierta por Caja ID
        $exists = CierreCaja::where('caja_id', $data['caja_id'])
            ->whereNull('fecha_cierre')
            ->exists();

        if ($exists) {
            return back()->with('error', 'No se puede abrir la caja: Esta caja ya tiene una sesión activa.');
        }

        CierreCaja::create($data);

        return redirect()->route('cierre-caja.index')->with('success', 'Arqueo de caja registrado correctamente.');
    }

    public function show(CierreCaja $cierre)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Seguridad: Solo el dueño de la sesión abierta o un admin/supervisor puede acceder
        if (!$cierre->fecha_cierre && $cierre->user_id !== $user->id) {
            if (!$user->hasAnyRole(['super_admin', 'admin_empresa', 'supervisor'])) {
                return redirect()->route('cierre-caja.index')
                    ->with('error', 'No tienes permiso para acceder a esta sesión de caja abierta por otro usuario.');
            }
        }

        $movimientos = DB::select("( SELECT
                v.created_at AS fecha_emision,
                'Ingreso - Venta' AS operacion,
                'ingreso' AS tipo_movimiento,
                c.nombre AS cliente_nombre,
                CONCAT( v.serie, ' ', v.numero ) AS concepto,
                tp.nombre AS metodo_pago,
                tp.es_efectivo,
                CASE 
                    WHEN d.id IS NULL THEN v.total
                    ELSE (v.total - (d.monto_deuda + COALESCE((SELECT SUM(monto) FROM deuda_pagos WHERE deuda_id = d.id), 0)))
                END AS importe,
                u.name AS usuario,
                v.id_venta AS id_movimiento,
                'venta' AS origen_movimiento
                FROM
                    ventas v
                    INNER JOIN clientes c ON c.id = v.id_cliente
                    INNER JOIN users u ON u.id = v.id_usuario 
                    LEFT JOIN tipos_pagos tp ON tp.id = v.id_tipo_pago
                    LEFT JOIN deudas d ON d.venta_id = v.id_venta
                WHERE
                    v.cierre_caja_id = :cierre_id AND v.estado != 0 
                ) UNION
                (
                SELECT
                    o.created_at AS fecha_emision,
                    o.partida AS operacion,
                    o.tipo AS tipo_movimiento,
                    o.tipo AS cliente_nombre,
                    o.concepto,
                    o.metodo_pago,
                    o.es_efectivo,
                    o.importe,
                    u.name AS usuario,
                    o.id AS id_movimiento,
                    'operacion' AS origen_movimiento
                FROM
                    operaciones_caja o
                INNER JOIN users u ON u.id = o.user_id 
                where o.cierre_caja_id = :cierre_id_2
                ) ORDER BY fecha_emision DESC", ['cierre_id' => $cierre->id, 'cierre_id_2' => $cierre->id]);

        $ingresosPorMetodo = [];
        foreach ($movimientos as $mov) {
            $importe = floatval($mov->importe);
            $esEfectivo = (bool) ($mov->es_efectivo ?? false);
            $metodoPago = $mov->metodo_pago ?? 'Sin método';

            if (strtolower($mov->tipo_movimiento ?? '') === 'ingreso' && !$esEfectivo) {
                if (!isset($ingresosPorMetodo[$metodoPago])) {
                    $ingresosPorMetodo[$metodoPago] = 0;
                }
                $ingresosPorMetodo[$metodoPago] += $importe;
            }
        }

        // Si el cierre está abierto, calculamos los totales dinámicamente
        if (!$cierre->fecha_cierre) {
            $ingresosTotal = 0;
            $egresosTotal = 0;
            $aportacionesTotal = 0;
            $sustraccionesTotal = 0;

            // Totales exclusivos para el balance de EFECTIVO
            $ingresosEfectivo = 0;
            $egresosEfectivo = 0;
            $aportacionesEfectivo = 0;
            $sustraccionesEfectivo = 0;

            foreach ($movimientos as $mov) {
                $importe = floatval($mov->importe);
                $esEfectivo = (bool) ($mov->es_efectivo ?? false);
                $tipoMov = strtolower($mov->tipo_movimiento ?? '');

                switch ($tipoMov) {
                    case 'ingreso':
                        $ingresosTotal += $importe;
                        if ($esEfectivo)
                            $ingresosEfectivo += $importe;
                        break;
                    case 'gasto':
                        $egresosTotal += $importe;
                        if ($esEfectivo)
                            $egresosEfectivo += $importe;
                        break;
                    case 'aportacion':
                    case 'aporte':
                        $aportacionesTotal += $importe;
                        if ($esEfectivo)
                            $aportacionesEfectivo += $importe;
                        break;
                    case 'sustraccion':
                    case 'retiro':
                        $sustraccionesTotal += $importe;
                        if ($esEfectivo)
                            $sustraccionesEfectivo += $importe;
                        break;
                }
            }

            // Para la vista principal de arqueo, usamos los totales de EFECTIVO
            // ya que se asume que el cierre es del cajón de dinero.
            $cierre->ingresos = $ingresosEfectivo;
            $cierre->egresos = $egresosEfectivo;
            $cierre->aportaciones = $aportacionesEfectivo;
            $cierre->sustracciones = $sustraccionesEfectivo;

            // Podemos pasar los totales generales a la vista si fuera necesario, 
            // pero por ahora priorizamos que el teórico cuadre con el efectivo.
        }

        return view('cierres.show', ['cierre' => $cierre, 'movimientos' => $movimientos, 'ingresosPorMetodo' => $ingresosPorMetodo]);
    }

    public function close(Request $request, CierreCaja $cierre)
    {
        $data = $request->validate([
            'monto_apertura' => 'required|numeric',
            'monto_cierre' => 'required|numeric',
            'ingresos' => 'nullable|numeric',
            'egresos' => 'nullable|numeric',
            'aportaciones' => 'nullable|numeric',
            'sustracciones' => 'nullable|numeric',
            'observaciones' => 'nullable|string',
        ]);

        $cierre->monto_apertura = $data['monto_apertura'];
        $cierre->monto_cierre = $data['monto_cierre'];
        $cierre->ingresos = $data['ingresos'] ?? 0;
        $cierre->egresos = $data['egresos'] ?? 0;
        $cierre->aportaciones = $data['aportaciones'] ?? 0;
        $cierre->sustracciones = $data['sustracciones'] ?? 0;
        $cierre->observaciones = $data['observaciones'] ?? null;
        $cierre->fecha_cierre = now();
        $cierre->save();

        return response()->json(['success' => true, 'message' => 'Caja cerrada correctamente']);
    }

    /**
     * Endpoint para que el POS consulte si el usuario tiene una caja abierta
     */
    public function getOpenCaja(Request $request)
    {
        $user = Auth::user();
        $selectedCajaId = session('selected_caja_id');

        $openCaja = CierreCaja::where('user_id', $user->id)
            ->where('caja_id', $selectedCajaId)
            ->whereNull('fecha_cierre')
            ->first();

        if ($openCaja) {
            // Obtener ventas asociadas a esta caja (si la columna existe)
            $ventas = [];
            try {
                $ventas = Venta::where('cierre_caja_id', $openCaja->id)
                    ->select('id_venta', 'serie', 'numero', 'total', 'fecha_emision')
                    ->orderBy('fecha_emision', 'desc')
                    ->get();
            } catch (\Throwable $e) {
                // Si la columna no existe o hay error, simplemente ignorar
                $ventas = [];
            }

            return response()->json([
                'open' => true,
                'caja' => [
                    'id' => $openCaja->id,
                    'ingresos' => $openCaja->ingresos ?? 0,
                    'egresos' => $openCaja->egresos ?? 0,
                    'observaciones' => $openCaja->observaciones ?? '',
                    'ventas' => $ventas
                ]
            ]);
        }

        return response()->json(['open' => false, 'caja' => null]);
    }
}
