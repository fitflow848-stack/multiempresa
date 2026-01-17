<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Partida;

class PartidaController extends Controller
{
    public function index()
    {
        $partidas = Partida::orderBy('nombre')->get();
        return response()->json($partidas);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        $partida = Partida::create($data);
        return response()->json(['success' => true, 'partida' => $partida]);
    }
}
