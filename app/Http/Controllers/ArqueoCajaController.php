<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ArqueoCaja;
use Illuminate\Support\Facades\Auth;

class ArqueoCajaController extends Controller
{

    public function index(Request $request)
    {
        $cierre_id = $request->get('cierre_id');
        $user = $request->user();

        $query = ArqueoCaja::query();
        if ($cierre_id) {
            $query->where('cierre_id', $cierre_id);
        } else if ($user) {
            $query->where('user_id', $user->id)->whereDate('created_at', now()->toDateString());
        }

        $arqueo = $query->orderBy('id', 'desc')->first();

        if ($arqueo) return response()->json(['found' => true, 'arqueo' => $arqueo]);
        return response()->json(['found' => false]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'monedas' => 'nullable|array',
            'billetes' => 'nullable|array',
            'total' => 'required|numeric',
            'total_caja' => 'nullable|numeric',
            'total_cierre' => 'nullable|numeric',
            'descuento' => 'nullable|numeric',
            'notas' => 'nullable|string',
            'cierre_id' => 'nullable|integer'
        ]);

        $userId = $request->user()?->id ?? null;
        $sucursalId = Auth::user()->branch_id ?? null;
        $cierreId = $data['cierre_id'] ?? null;

        // Buscar arqueo existente por cierre_id si se proporcionó
        $existing = null;
        if ($cierreId) {
            $existing = ArqueoCaja::where('cierre_id', $cierreId)->first();
        }

        // Fallback: arqueo del mismo usuario en el día
        if (!$existing) {
            $existing = ArqueoCaja::where('user_id', $userId)
                ->whereDate('created_at', now()->toDateString())
                ->orderBy('id', 'desc')
                ->first();
        }

        $payload = [
            'user_id' => $userId,
            'cierre_id' => $cierreId,
            'sucursal_id' => $sucursalId,
            'monedas' => $data['monedas'] ?? null,
            'billetes' => $data['billetes'] ?? null,
            'total' => $data['total'],
            'total_caja' => $data['total_caja'] ?? null,
            'total_cierre' => $data['total_cierre'] ?? null,
            'descuento' => $data['descuento'] ?? null,
            'ultimo_conteo' => now(),
            'notas' => $data['notas'] ?? null,
        ];

        if ($existing) {
            $existing->update($payload);
            return response()->json(['success' => true, 'updated' => true, 'id' => $existing->id, 'arqueo' => $existing]);
        }

        $arqueo = ArqueoCaja::create(array_merge($payload, ['fecha' => now()]));
        return response()->json(['success' => true, 'created' => true, 'id' => $arqueo->id, 'arqueo' => $arqueo]);
    }
}
