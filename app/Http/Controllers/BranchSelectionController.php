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

        $branches = $user->branches;

        if ($branches->count() <= 1) {
            $branch = $branches->first();
            if ($branch) {
                session([
                    'active_branch_id' => $branch->id,
                    'branch_selected' => true
                ]);
                return redirect()->route('principal.index');
            }
            if ($user->isAdminEmpresa()) {
                session(['branch_selected' => true]);
                return redirect()->route('principal.index');
            }
            abort(403, 'No tienes sucursales asignadas.');
        }

        return view('auth.select-branch', compact('branches'));
    }

    public function select(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:sucursales,id',
        ]);

        $user = Auth::user();
        
        // Verificar que el usuario tenga acceso a esa sucursal
        if (!$user->branches->contains($request->branch_id) && !$user->hasRole('super_admin')) {
             abort(403, 'No tienes acceso a esta sucursal.');
        }

        session([
            'active_branch_id' => $request->branch_id,
            'branch_selected' => true
        ]);

        return redirect()->route('principal.index');
    }
}
