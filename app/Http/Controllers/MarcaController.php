<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class MarcaController extends Controller
{
    /**
     * Store a newly created Marca.
     * Returns JSON { id, nombre } on success.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
        ]);

        // Evitar duplicados por nombre (case-insensitive simple check)
        $existing = Marca::whereRaw('LOWER(nombre) = ?', [mb_strtolower($data['nombre'])])->first();
        if ($existing) {
            return response()->json($existing, 200);
        }

        $marca = Marca::create([
            'nombre' => $data['nombre'],
        ]);

        return response()->json($marca, 201);
    }

    /**
     * Optional: list marcas for selects/autocomplete
     */
    public function index(Request $request)
    {
        $q = $request->get('q','');
        $query = Marca::query();
        if ($q !== '') {
            $query->where('nombre', 'like', "%{$q}%");
        }
        $items = $query->orderBy('nombre')->limit(50)->get(['id','nombre']);
        return response()->json($items);
    }
}