<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware EnsureCompanyScope
 *
 * Garantiza que los usuarios (excepto super_admin) tengan una empresa asignada.
 * Comparte variables de contexto multi-tenant en todas las vistas.
 */
class EnsureCompanyScope
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return $next($request);
        }

        // Super Admin: acceso total sin restricciones, pero compartimos contexto si lo tiene
        if ($user->hasRole('super_admin')) {
            $activeCompanyId = session('active_company_id');
            $activeBranchId = session('active_branch_id');
            view()->share('is_super_admin', true);
            view()->share('current_company_id', $activeCompanyId);
            view()->share('current_branch_id', $activeBranchId);
            view()->share('current_user_cajas', collect());

            if ($activeBranchId) {
                view()->share('current_branch', \App\Models\Sucursal::find($activeBranchId));
            }

            // Establecer el team id para el filtrado de roles/permisos
            if ($activeCompanyId) {
                setPermissionsTeamId($activeCompanyId);
            }

            return $next($request);
        }

        // Usuarios normales: deben tener empresa asignada
        if (!$user->company_id) {
            abort(403, 'Tu usuario no tiene una empresa asignada. Contacta al administrador.');
        }

        // Compartir variables de contexto multi-tenant en las vistas
        view()->share('is_super_admin', false);
        view()->share('current_company_id', $user->company_id);
        view()->share('current_company', $user->company);
        view()->share('current_branch_id', $user->branch_id);
        view()->share('current_branch', $user->branch);

        // Cargar las cajas disponibles según el rol del usuario
        $cajasDisponibles = $user->cajasDisponibles();
        view()->share('current_user_cajas', $cajasDisponibles);
        view()->share('selected_caja_id', session('selected_caja_id'));

        // Si el usuario tiene sucursal, compartir su logo
        if ($user->branch) {
            view()->share('current_branch_logo', $user->branch->logo_url);
        }

        // Establecer el team id para el filtrado de roles/permisos
        setPermissionsTeamId($user->company_id);

        return $next($request);
    }
}
