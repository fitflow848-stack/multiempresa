<?php

namespace App\Http\Controllers;

use App\Models\Laboratorio;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class LaboratorioController extends Controller
{
    /**
     * Store a newly created Laboratorio.
     * Returns JSON { id, nombre } on success.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
        ]);

        // Evitar duplicados por nombre (case-insensitive simple check)
        $existing = Laboratorio::whereRaw('LOWER(nombre) = ?', [mb_strtolower($data['nombre'])])->first();
        if ($existing) {
            return response()->json($existing, 200);
        }

        $laboratorio = Laboratorio::create([
            'nombre' => $data['nombre'],
        ]);

        return response()->json($laboratorio, 201);
    }

    /**
     * Optional: list laboratorios for selects/autocomplete
     */
    public function index(Request $request)
    {
        $q = $request->get('q','');
        $query = Laboratorio::query();
        if ($q !== '') {
            $query->where('nombre', 'like', "%{$q}%");
        }
        $items = $query->orderBy('nombre')->limit(50)->get(['id','nombre']);
        return response()->json($items);
    }
}
