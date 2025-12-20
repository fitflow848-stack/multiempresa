<?php

namespace App\Http\Controllers;

use App\Models\SubFamilia;
use App\Models\Familia;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SubfamiliaController extends Controller
{
    // Listado de subfamilias (opcional, con filtro por familia_id)
    public function index(Request $request)
    {
        $q = $request->get('q', '');
        $familiaId = $request->get('familia_id', null);

        $query = Subfamilia::query();
        if ($familiaId) {
            $query->where('familia_id', $familiaId);
        }
        if ($q !== '') {
            $query->where('nombre', 'like', "%{$q}%");
        }

        $items = $query->orderBy('nombre')->limit(200)->get(['id','familia_id','nombre']);
        return response()->json($items);
    }

    // Guardar nueva subfamilia asociada a una familia
    public function store(Request $request)
    {
        $data = $request->validate([
            'familia_id' => ['required', 'exists:familias,id'],
            'nombre' => ['required', 'string', 'max:255'],
        ]);

        // Evitar duplicados dentro de la misma familia (case-insensitive)
        $existing = Subfamilia::where('familia_id', $data['familia_id'])
            ->whereRaw('LOWER(nombre) = ?', [mb_strtolower($data['nombre'])])
            ->first();

        if ($existing) {
            return response()->json($existing, 200);
        }

        $sub = Subfamilia::create($data);
        return response()->json($sub, 201);
    }
}