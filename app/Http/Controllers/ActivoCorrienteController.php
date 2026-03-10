<?php

namespace App\Http\Controllers;

use App\Models\ActivoCorriente;
use App\Models\TipoActivoCorriente;
use Illuminate\Http\Request;

class ActivoCorrienteController extends Controller
{
    public function index(Request $request)
    {
        // Asegurar que la empresa tenga los tipos por defecto
        \App\Helpers\AccountingHelper::ensureDefaults(auth()->user()->company_id);

        $tipos = TipoActivoCorriente::orderBy('nombre')
            ->get();

        $query = ActivoCorriente::with('tipo')
            ->where(function($q) {
                $q->where('tipo_adelanto', '!=', 'personal')
                  ->orWhereNull('tipo_adelanto');
            })
            ->orderBy('fecha_registro', 'desc');

        if ($request->filled('tipo_id')) {
            $query->where('tipo_activo_corriente_id', $request->tipo_id);
        }
        if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecha_registro', '>=', $request->fecha_inicio);
        }
        if ($request->filled('fecha_fin')) {
            $query->whereDate('fecha_registro', '<=', $request->fecha_fin);
        }

        $activos = $query->paginate(20);

        return view('activos_corrientes.index', compact('tipos', 'activos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo_activo_corriente_id' => 'required|exists:tipo_activo_corrientes,id',
            'nombre' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0',
            'fecha_registro' => 'required|date',
            'documento' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string'
        ]);

        ActivoCorriente::create($request->all());

        return redirect()->route('activos_corrientes.index')->with('success', 'Activo corriente registrado correctamente');
    }

    public function storeTipo(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|unique:tipo_activo_corrientes,nombre|max:255',
            'descripcion' => 'nullable|string'
        ]);

        $tipo = TipoActivoCorriente::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tipo de activo corriente creado exitosamente',
                'tipo' => $tipo
            ]);
        }

        return redirect()->route('activos_corrientes.index')->with('success', 'Tipo creado');
    }

    public function destroy($id)
    {
        $activo = ActivoCorriente::findOrFail($id);
        $activo->delete();

        return redirect()->route('activos_corrientes.index')->with('success', 'Eliminado correctamente');
    }

    public function edit($id)
    {
        $activo = ActivoCorriente::findOrFail($id);
        return response()->json($activo);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tipo_activo_corriente_id' => 'required|exists:tipo_activo_corrientes,id',
            'nombre' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0',
            'fecha_registro' => 'required|date',
            'documento' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string'
        ]);

        $activo = ActivoCorriente::findOrFail($id);
        $activo->update($request->all());

        return redirect()->route('activos_corrientes.index')->with('success', 'Activo actualizado correctamente');
    }
}
