<?php

namespace App\Http\Controllers;

use App\Models\UnidadMedida;
use Illuminate\Http\Request;

class UnidadMedidaController extends Controller
{
    /**
     * Store a newly created UnidadMedida.
     * Accepts { codigo, nombre }.
     * Returns JSON { id, codigo, nombre }.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'codigo' => ['required','string','max:20'],
            'nombre' => ['required','string','max:255'],
        ]);

        // Avoid duplicates by codigo (case-insensitive)
        $existing = UnidadMedida::whereRaw('LOWER(codigo) = ?', [mb_strtolower($data['codigo'])])->first();
        if ($existing) {
            return response()->json($existing, 200);
        }

        $unidad = UnidadMedida::create($data);

        return response()->json($unidad, 201);
    }

    /**
     * Optional: list unidades for selects/autocomplete
     */
    public function index(Request $request)
    {
        $q = $request->get('q','');
        $query = UnidadMedida::query();
        if ($q !== '') {
            $query->where(function($sub) use ($q) {
                $sub->where('codigo','like',"%{$q}%")
                    ->orWhere('nombre','like',"%{$q}%");
            });
        }
        $items = $query->orderBy('nombre')->limit(50)->get(['id','codigo','nombre']);
        return response()->json($items);
    }
}