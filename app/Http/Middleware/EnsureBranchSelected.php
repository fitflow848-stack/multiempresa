<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBranchSelected
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user && !$user->isSuperAdmin()) {
            // No aplicar a rutas de seleccion o logout
            if ($request->routeIs('branch.select') || $request->routeIs('branch.select.post') || $request->routeIs('logout')) {
                return $next($request);
            }

            // Si no ha seleccionado sucursal en esta sesion
            if (!session('branch_selected')) {
                $branchesCount = $user->branches()->count();

                if ($branchesCount > 1) {
                    return redirect()->route('branch.select');
                } elseif ($branchesCount === 1) {
                    $branch = $user->branches->first();
                    session([
                        'active_branch_id' => $branch->id,
                        'branch_selected' => true
                    ]);
                } else {
                    // Si no tiene sucursales pero no es super admin, deberia estar bloqueado o ser admin_empresa
                    if (!$user->isAdminEmpresa()) {
                        // abort(403, 'No tienes sucursales asignadas.');
                    }
                    session(['branch_selected' => true]);
                }
            }
        }

        return $next($request);
    }
}
