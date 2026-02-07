<?php

namespace App\Http\Controllers;

use App\Models\Pasivo;
use App\Models\TipoPasivo;
use Illuminate\Http\Request;

class PasivoController extends Controller
{
    public function index(Request $request)
    {
        $tipos = TipoPasivo::orderBy('nombre')->get();

        $query = Pasivo::with('tipo')->orderBy('fecha_registro', 'desc');

        if ($request->filled('tipo_id')) {
            $query->where('tipo_pasivo_id', $request->tipo_id);
        }
        if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecha_registro', '>=', $request->fecha_inicio);
        }
        if ($request->filled('fecha_fin')) {
            $query->whereDate('fecha_registro', '<=', $request->fecha_fin);
        }

        $pasivos = $query->paginate(20);

        return view('pasivos.index', compact('tipos', 'pasivos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo_pasivo_id' => 'required|exists:tipo_pasivos,id',
            'nombre' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0',
            'fecha_registro' => 'required|date',
            'documento' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string'
        ]);

        Pasivo::create($request->all());

        return redirect()->route('pasivos.index')->with('success', 'Pasivo registrado correctamente');
    }

    public function storeTipo(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|unique:tipo_pasivos,nombre|max:255',
            'descripcion' => 'nullable|string'
        ]);

        $tipo = TipoPasivo::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tipo de pasivo creado exitosamente',
                'tipo' => $tipo
            ]);
        }

        return redirect()->route('pasivos.index')->with('success', 'Tipo de pasivo creado');
    }

    public function destroy($id)
    {
        $pasivo = Pasivo::findOrFail($id);
        $pasivo->delete();

        return redirect()->route('pasivos.index')->with('success', 'Pasivo eliminado correctamente');
    }
}
