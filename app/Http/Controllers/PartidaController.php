<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Partida;
use Illuminate\Support\Facades\Auth;

class PartidaController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $partidas = Partida::where(function($query) use ($user) {
                $query->where('company_id', $user->company_id)
                      ->orWhereNull('company_id');
            })
            ->orderBy('nombre')
            ->get();
        return response()->json($partidas);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        $data['company_id'] = Auth::user()->company_id;

        $partida = Partida::create($data);
        return response()->json(['success' => true, 'partida' => $partida]);
    }
}
