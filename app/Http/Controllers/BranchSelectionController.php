<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchSelectionController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) return redirect('/login');

        // Lógica de obtención de sucursales según rol
        if ($user->hasRole('super_admin')) {
            $branches = Sucursal::activas()->get();
        } elseif ($user->isAdminEmpresa()) {
            // El administrador de empresa puede ver TODAS las sucursales de su empresa
            $branches = $user->company->sucursales()->activas()->get();
        } else {
            // Usuarios normales (vendedor, etc.) solo ven las que tienen asignadas vía pivot
            $branches = $user->branches()->activas()->get();
        }

        if ($branches->count() <= 1) {
            $branch = $branches->first();
            if ($branch) {
                session([
                    'active_branch_id' => $branch->id,
                    'active_company_id' => $branch->company_id, // Store active company
                    'branch_selected' => true
                ]);
                return redirect()->route('principal.index');
            }
            if ($user->isAdminEmpresa()) {
                session(['branch_selected' => true, 'active_company_id' => $user->company_id]);
                return redirect()->route('principal.index');
            }
            abort(403, 'No tienes sucursales activas asignadas.');
        }

        return view('auth.select-branch', compact('branches'));
    }

    public function select(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:sucursales,id',
        ]);

        $user = Auth::user();
        
        $branch = Sucursal::find($request->branch_id);
        
        // Verificar que el usuario tenga acceso a esa sucursal
        if ($user->isAdminEmpresa()) {
            // Un admin_empresa puede elegir CUALQUIERA de su propia empresa
            $hasAccess = $branch && $branch->company_id == $user->company_id;
        } elseif ($user->hasRole('super_admin')) {
            $hasAccess = true;
        } else {
            // Usuarios operativos: deben estar en el pivot branches_user
            $hasAccess = $user->branches->contains($request->branch_id);
        }

        if (!$hasAccess) {
             abort(403, 'No tienes acceso a esta sucursal.');
        }

        session([
            'active_branch_id' => $request->branch_id,
            'active_company_id' => $branch->company_id, // Store active company
            'branch_selected' => true
        ]);

        return redirect()->route('principal.index');
    }
}
