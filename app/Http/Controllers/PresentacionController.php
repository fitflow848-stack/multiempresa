<?php

namespace App\Http\Controllers;

use App\Models\Presentacion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PresentacionController extends Controller
{
    public function index()
    {
        $presentaciones = Presentacion::activo()->orderBy('nombre')->get();
        return response()->json($presentaciones);
    }

    public function store(Request $request)
    {
        $user = \App\Helpers\AuthHelper::resolveAuthenticatedUser();
        $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('presentaciones', 'nombre')->where('company_id', $user?->company_id)
            ],
            'descripcion' => 'nullable|string|max:1000'
        ]);

        $presentacion = Presentacion::create([
            'nombre' => strtoupper(trim($request->nombre)),
            'descripcion' => $request->descripcion,
            'activo' => true
        ]);

        return response()->json([
            'success' => true,
            'data' => $presentacion,
            'message' => 'Presentación creada correctamente'
        ]);
    }

    public function show(Presentacion $presentacion)
    {
        return response()->json($presentacion);
    }

    public function update(Request $request, Presentacion $presentacion)
    {
        $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('presentaciones', 'nombre')
                    ->ignore($presentacion->id)
                    ->where('company_id', \App\Helpers\AuthHelper::resolveAuthenticatedUser()?->company_id)
            ],
            'descripcion' => 'nullable|string|max:1000',
            'activo' => 'boolean'
        ]);

        $presentacion->update([
            'nombre' => strtoupper(trim($request->nombre)),
            'descripcion' => $request->descripcion,
            'activo' => $request->boolean('activo', true)
        ]);

        return response()->json([
            'success' => true,
            'data' => $presentacion,
            'message' => 'Presentación actualizada correctamente'
        ]);
    }

    public function destroy(Presentacion $presentacion)
    {
        // Soft delete - marcar como inactivo en lugar de eliminar
        $presentacion->update(['activo' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Presentación desactivada correctamente'
        ]);
    }

    public function activate(Presentacion $presentacion)
    {
        $presentacion->update(['activo' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Presentación activada correctamente'
        ]);
    }
}
