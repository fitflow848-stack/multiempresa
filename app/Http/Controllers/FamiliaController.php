<?php

namespace App\Http\Controllers;

use App\Models\Familia;
use Illuminate\Http\Request;

class FamiliaController extends Controller
{
    // Listar familias (para selects / autocomplete)
    public function index(Request $request)
    {
        $q = $request->get('q', '');
        $query = Familia::query();
        if ($q !== '') {
            $query->where('nombre', 'like', "%{$q}%");
        }
        $items = $query->orderBy('nombre')->limit(100)->get(['id', 'nombre']);
        return response()->json($items);
    }

    // Guardar nueva familia (evita duplicados case-insensitive)
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
        ]);

        $existing = Familia::whereRaw('LOWER(nombre) = ?', [mb_strtolower($data['nombre'])])->first();
        if ($existing) {
            return response()->json($existing, 200);
        }

        $familia = Familia::create(['nombre' => $data['nombre']]);
        return response()->json($familia, 201);
    }

    // Listar subfamilias de una familia específica
    public function subfamilias(Familia $familia, Request $request)
    {
        $q = $request->get('q', '');
        $query = $familia->subfamilias();
        if ($q !== '') {
            $query->where('nombre', 'like', "%{$q}%");
        }
        $items = $query->orderBy('nombre')->limit(200)->get(['id', 'nombre']);
        return response()->json($items);
    }
}