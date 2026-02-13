<?php

namespace App\Http\Controllers;

use App\Models\Aporte;
use App\Models\TipoAporte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AporteController extends Controller
{
    public function index(Request $request)
    {
        $tipos = TipoAporte::orderBy('nombre')->get();

        $query = Aporte::with('tipo')->orderBy('fecha_registro', 'desc');

        if ($request->filled('tipo_id')) {
            $query->where('tipo_aporte_id', $request->tipo_id);
        }
        if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecha_registro', '>=', $request->fecha_inicio);
        }
        if ($request->filled('fecha_fin')) {
            $query->whereDate('fecha_registro', '<=', $request->fecha_fin);
        }

        $aportes = $query->paginate(20);

        return view('aportes.index', compact('tipos', 'aportes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo_aporte_id' => 'required|exists:tipo_aportes,id',
            'nombre' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0',
            'fecha_registro' => 'required|date',
            'documento' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string'
        ]);

        $aporte = new Aporte($request->all());
        $aporte->user_id = Auth::id();
        $aporte->save();

        return redirect()->route('aportes.index')->with('success', 'Aporte registrado correctamente');
    }

    public function destroy($id)
    {
        $aporte = Aporte::findOrFail($id);
        $aporte->delete();

        return redirect()->route('aportes.index')->with('success', 'Aporte eliminado correctamente');
    }

    public function storeTipo(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|unique:tipo_aportes,nombre|max:255',
            'descripcion' => 'nullable|string'
        ]);

        $tipo = TipoAporte::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tipo de aporte creado exitosamente',
                'tipo' => $tipo
            ]);
        }

        return redirect()->route('aportes.index')->with('success', 'Tipo de aporte creado');
    }
}
