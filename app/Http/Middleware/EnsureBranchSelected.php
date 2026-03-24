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
            // Verificar que la sucursal en sesión sea válida para este usuario/empresa
            $currentActiveBranchId = session('active_branch_id');
            if ($currentActiveBranchId) {
                $isSameCompany = \App\Models\Sucursal::where('id', $currentActiveBranchId)
                    ->where('company_id', $user->company_id)
                    ->exists();
                
                if (!$isSameCompany) {
                    // Si no es de la misma empresa, limpiamos para obligar re-selección
                    session()->forget(['active_branch_id', 'branch_selected']);
                }
            }

            // No aplicar a rutas de seleccion o logout
            if ($request->routeIs('branch.select') || $request->routeIs('branch.select.post') || $request->routeIs('logout') || $request->routeIs('logout.get')) {
                return $next($request);
            }

            // Si no ha seleccionado sucursal en esta sesion
            if (!session('branch_selected')) {
                // Si es admin_empresa, puede entrar a CUALQUIERA de su empresa.
                // Los demás roles solo las que tengan asignadas en el pivot.
                if ($user->isAdminEmpresa()) {
                    $branches = $user->company->sucursales ?? collect();
                } else {
                    $branches = $user->branches ?? collect();
                }

                $branchesCount = $branches->count();

                if ($branchesCount > 1) {
                    return redirect()->route('branch.select');
                } elseif ($branchesCount === 1) {
                    $branch = $branches->first();
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
