<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OperacionCaja;
use App\Models\CierreCaja;
use Illuminate\Support\Facades\Auth;

class OperacionCajaController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'cierre_caja_id' => 'nullable|exists:cierre_cajas,id',
            'tipo' => 'required|in:aportacion,sustraccion,ingreso,gasto',
            'partida' => 'nullable|string|max:255',
            'concepto' => 'nullable|string',
            'importe' => 'required|numeric',
        ]);

        $data['user_id'] = Auth::id();

        $operacion = OperacionCaja::create($data);

        if (!empty($data['cierre_caja_id'])) {
            $cierre = CierreCaja::find($data['cierre_caja_id']);
            if ($cierre) {
                switch ($data['tipo']) {
                    case 'ingreso':
                        $cierre->ingresos = ($cierre->ingresos ?? 0) + $data['importe'];
                        break;
                    case 'gasto':
                        $cierre->egresos = ($cierre->egresos ?? 0) + $data['importe'];
                        break;
                    case 'aportacion':
                        $cierre->aportaciones = ($cierre->aportaciones ?? 0) + $data['importe'];
                        break;
                    case 'sustraccion':
                        $cierre->sustracciones = ($cierre->sustracciones ?? 0) + $data['importe'];
                        break;
                }
                $cierre->save();
            }
        }

        return response()->json(['success' => true, 'operacion' => $operacion]);
    }
}
