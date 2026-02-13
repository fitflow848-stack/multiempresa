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

        // Guardar el ID de la caja en la sesión
        session(['selected_caja_id' => $request->caja_id]);

        return back()->with('success', 'Caja seleccionada correctamente.');
    }
}
