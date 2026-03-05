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

        $isTesoreria = $request->has('tipo') && $request->tipo === 'tesoreria';

        $query = CierreCaja::with(['user', 'caja'])->latest();

        // Separar visiblemente las cajas normales de las bóvedas/tesorerías
        if ($isTesoreria) {
            $query->whereHas('caja', function($q) {
                $q->where('is_boveda', true);
            });
        } else {
            $query->whereHas('caja', function($q) {
                $q->where('is_boveda', false);
            });
        }

        // Filtro por empresa
        $query->where('id_empresa', $user->company_id);

        // Filtro por sucursal activa
        if ($user->hasAnyRole(['admin_empresa', 'supervisor']) && $user->branch_id) {
            $query->whereHas('caja', function($q) use ($user) {
                $q->where('sucursal_id', $user->branch_id);
            });
        }

        // Lógica de filtro por caja: si selecciona una explícitamente, o si tiene una en sesión
        $filtroCajaId = null;
        if ($request->has('caja_id')) {
            $filtroCajaId = $request->caja_id; // Puede ser un ID o '' (Todas)
        } else {
            // Por defecto, usar la de sesión si existe y coincide con el tipo (Bóveda vs Normal)
            if ($selectedCajaId) {
                $cajaSesion = Caja::find($selectedCajaId);
                if ($cajaSesion && (bool)$cajaSesion->is_boveda === $isTesoreria) {
                    $filtroCajaId = $selectedCajaId;
                }
            }
        }

        // Aplicar el ID de caja a la consulta si no está vacío
        if (!empty($filtroCajaId)) {
            $query->where('caja_id', $filtroCajaId);
        } elseif (!$user->hasAnyRole(['super_admin', 'admin_empresa', 'supervisor'])) {
            // Un usuario normal DEBE tener una caja asignada por sesión para ver. 
            // Si la caja seleccionada no es del tipo actual, aún forzamos filtro por su ID de caja asignada 
            // (que hará que no vea nada) o simplemente filtraremos a sus cajas asignadas de este tipo.
            // Para mantener compatibilidad con el diseño original:
            if ($selectedCajaId) {
                $query->where('caja_id', $selectedCajaId);
            }
        }

        // Si no es admin/supervisor, solo ve sus propios cierres
        if (!$user->hasAnyRole(['super_admin', 'admin_empresa', 'supervisor'])) {
            $query->where('user_id', $user->id);
        } else {
            // Filtro manual de usuario
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }
        }

        // Aplicar filtros de fecha
        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        $cierres = $query->paginate(20);

        // Actualizar dinámicamente si están abiertas para que la vista index las muestre correctas
        foreach ($cierres as $cierre) {
            if (is_null($cierre->fecha_cierre)) {
                $totales = $cierre->calcularTotalesDinamicos();
                $cierre->ingresos = $totales['ingresos_efectivo'];
                $cierre->egresos = $totales['egresos_efectivo'];
                $cierre->aportaciones = $totales['aportaciones_efectivo'];
                $cierre->sustracciones = $totales['sustracciones_efectivo'];
            }
        }

        // Para tesorería: la bóveda es compartida, verificar si hay ALGUNA sesión
        // abierta de bóveda en la empresa (sin importar quién la abrió)
        if ($isTesoreria) {
            $openCaja = CierreCaja::where('id_empresa', $user->company_id)
                ->whereHas('caja', fn($q) => $q->where('is_boveda', true))
                ->whereNull('fecha_cierre')
                ->latest()
                ->first();
        } elseif ($selectedCajaId) {
            $openCaja = CierreCaja::where('caja_id', $selectedCajaId)
                ->whereNull('fecha_cierre')
                ->latest()
                ->first();
        } else {
            $openCaja = CierreCaja::where('user_id', $user->id)
                ->whereHas('caja', fn($q) => $q->where('is_boveda', false))
                ->whereNull('fecha_cierre')
                ->latest()
                ->first();
        }

        return view('cierres.index', compact('cierres', 'openCaja', 'isTesoreria', 'filtroCajaId'));
    }

    public function create(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $selectedCajaId = session('selected_caja_id');
        $isTesoreria = $request->has('tipo') && $request->tipo === 'tesoreria';

        if ($isTesoreria) {
            // Para la bóveda, usar withoutGlobalScopes() para evitar interferencia de traits.
            $cajaBoveda = Caja::withoutGlobalScopes()
                ->where('is_boveda', true)
                ->where('is_active', true)
                ->where('company_id', $user->company_id)
                ->when($user->branch_id, fn($q) => $q->where('sucursal_id', $user->branch_id))
                ->first();

            // Si no existe bóveda, crearla automáticamente para esta sucursal
            if (!$cajaBoveda) {
                $cajaBoveda = Caja::create([
                    'company_id'  => $user->company_id,
                    'sucursal_id' => $user->branch_id,
                    'nombre'      => 'Bóveda General',
                    'descripcion' => 'Bóveda creada automáticamente',
                    'is_active'   => true,
                    'is_boveda'   => true,
                ]);
            }

            // Verificar si ya hay una sesión abierta para esta bóveda
            $openCaja = CierreCaja::where('caja_id', $cajaBoveda->id)
                ->whereNull('fecha_cierre')
                ->first();

            if ($openCaja) {
                // Redirigir al show si ya está abierta (cualquier usuario la comparte)
                return redirect()->route('cierre-caja.show', $openCaja->id)
                    ->with('info', 'La bóveda ya está abierta.');
            }

            // Guardar en sesión para que store() lo use
            session(['boveda_caja_id' => $cajaBoveda->id]);

            $ultimoCierre = CierreCaja::where('caja_id', $cajaBoveda->id)
                ->whereNotNull('fecha_cierre')
                ->orderBy('created_at', 'desc')
                ->first();

            $saldoInicial = $ultimoCierre ? $ultimoCierre->monto_cierre : 0.00;

            return view('cierres.create', compact('saldoInicial', 'ultimoCierre', 'isTesoreria'));
        }

        // ---------- Flujo normal (caja regular) ----------
        if (!$selectedCajaId) {
            return redirect()->route('cierre-caja.index', ['tipo' => $request->tipo])->with('error', 'Debe seleccionar una caja activa en el menú superior antes de abrir una sesión.');
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

        return view('cierres.create', compact('saldoInicial', 'ultimoCierre', 'isTesoreria'));
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

        // Si viene de apertura de bóveda, usar el caja_id de la bóveda guardado en sesión
        $isTesoreria = $request->boolean('is_tesoreria', false);
        if ($isTesoreria && session('boveda_caja_id')) {
            $data['caja_id'] = session('boveda_caja_id');
            session()->forget('boveda_caja_id'); // Limpiar después de usar
        } else {
            $data['caja_id'] = session('selected_caja_id');
        }

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

        $redirectParams = $isTesoreria ? ['tipo' => 'tesoreria'] : [];
        return redirect()->route('cierre-caja.index', $redirectParams)->with('success', 'Arqueo de ' . ($isTesoreria ? 'Bóveda' : 'Caja') . ' registrado correctamente.');
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

        $totalesDin = $cierre->calcularTotalesDinamicos();
        $movimientos = $totalesDin['movimientos'];
        $ingresosPorMetodo = $totalesDin['ingresos_por_metodo'];

        // Si el cierre está abierto, usamos los totales dinámicos (calculados al vuelo)
        if (!$cierre->fecha_cierre) {
            $cierre->ingresos = $totalesDin['ingresos_efectivo'];
            $cierre->egresos = $totalesDin['egresos_efectivo'];
            $cierre->aportaciones = $totalesDin['aportaciones_efectivo'];
            $cierre->sustracciones = $totalesDin['sustracciones_efectivo'];

            // Calculamos el teórico acumulado para el balance
            $cierre->teorico_acumulado = $cierre->monto_apertura + $cierre->ingresos - $cierre->egresos + $cierre->aportaciones - $cierre->sustracciones;
            $cierre->descuatdre_calculado = $cierre->monto_cierre - $cierre->teorico_acumulado;
        }

        $isTesoreria = $cierre->caja ? $cierre->caja->is_boveda : false;

        return view('cierres.show', ['cierre' => $cierre, 'movimientos' => $movimientos, 'ingresosPorMetodo' => $ingresosPorMetodo, 'isTesoreria' => $isTesoreria]);
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
