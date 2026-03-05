<?php

namespace App\Http\Controllers;

use App\Models\Concentracion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ConcentracionController extends Controller
{
    public function index()
    {
        $concentraciones = Concentracion::activo()->orderBy('nombre')->get();
        return response()->json($concentraciones);
    }

    public function store(Request $request)
    {
        $user = \App\Helpers\AuthHelper::resolveAuthenticatedUser();
        $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('concentraciones', 'nombre')->where('company_id', $user->company_id)
            ],
            'descripcion' => 'nullable|string|max:1000',
            'unidad' => 'nullable|string|max:50'
        ]);

        $concentracion = Concentracion::create([
            'nombre' => strtoupper(trim($request->nombre)),
            'descripcion' => $request->descripcion,
            'unidad' => strtoupper(trim($request->unidad ?? '')),
            'activo' => true
        ]);

        return response()->json([
            'success' => true,
            'data' => $concentracion,
            'message' => 'Concentración creada correctamente'
        ]);
    }

    public function show(Concentracion $concentracion)
    {
        return response()->json($concentracion);
    }

    public function update(Request $request, Concentracion $concentracion)
    {
        $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('concentraciones', 'nombre')
                    ->ignore($concentracion->id)
                    ->where('company_id', \App\Helpers\AuthHelper::resolveAuthenticatedUser()?->company_id)
            ],
            'descripcion' => 'nullable|string|max:1000',
            'unidad' => 'nullable|string|max:50',
            'activo' => 'boolean'
        ]);

        $concentracion->update([
            'nombre' => strtoupper(trim($request->nombre)),
            'descripcion' => $request->descripcion,
            'unidad' => strtoupper(trim($request->unidad ?? '')),
            'activo' => $request->boolean('activo', true)
        ]);

        return response()->json([
            'success' => true,
            'data' => $concentracion,
            'message' => 'Concentración actualizada correctamente'
        ]);
    }

    public function destroy(Concentracion $concentracion)
    {
        // Soft delete - marcar como inactivo en lugar de eliminar
        $concentracion->update(['activo' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Concentración desactivada correctamente'
        ]);
    }

    public function activate(Concentracion $concentracion)
    {
        $concentracion->update(['activo' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Concentración activada correctamente'
        ]);
    }
}
