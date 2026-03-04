<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CajaSessionController extends Controller
{
    /**
     * Selecciona una caja para la sesión actual del usuario.
     */
    public function select(Request $request)
    {
        $request->validate([
            'caja_id' => 'required|exists:cajas,id',
        ]);

        $user = auth()->user();

        // Verificar si el usuario tiene acceso a esa caja
        if (!$user->tieneAccesoACaja($request->caja_id)) {
            return back()->with('error', 'No tienes permiso para acceder a esta caja.');
        }

        // Seguridad: No permitir seleccionar una caja que ya tiene una sesión abierta por OTRO usuario
        // A menos que sea super_admin o admin_empresa, quienes pueden entrar para auditar o cerrar
        $isAllowedOverride = $user->hasRole('super_admin') || $user->hasRole('admin_empresa');

        $openCaja = \App\Models\CierreCaja::where('caja_id', $request->caja_id)
            ->whereNull('fecha_cierre')
            ->first();

        if ($openCaja && $openCaja->user_id !== $user->id && !$isAllowedOverride) {
            return back()->with('error', 'La caja "' . $openCaja->caja->nombre . '" ya está siendo utilizada por el usuario ' . $openCaja->user->name . '. Debe esperar a que cierre su sesión.');
        }

        // Guardar el ID de la caja en la sesión
        session(['selected_caja_id' => $request->caja_id]);

        // Si el usuario es admin o superadmin y la caja pertenece a otra sucursal,
        // actualizar la sucursal activa para mantener la consistencia del sistema.
        $caja = \App\Models\Caja::withoutGlobalScope('sucursal')->find($request->caja_id);
        if ($caja && ($user->isAdminEmpresa() || $user->isSuperAdmin())) {
            if (session('active_branch_id') != $caja->sucursal_id) {
                session(['active_branch_id' => $caja->sucursal_id]);
                // No redirigir todavía, que siga el flujo normal
            }
        }

        return redirect()->route('pos.index')->with('success', 'Caja seleccionada correctamente.');
    }
}
