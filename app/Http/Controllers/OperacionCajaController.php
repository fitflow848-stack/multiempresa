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

    public function update(Request $request, $id)
    {
        $operacion = OperacionCaja::findOrFail($id);

        $data = $request->validate([
            'tipo' => 'required|in:aportacion,sustraccion,ingreso,gasto',
            'partida' => 'nullable|string|max:255',
            'concepto' => 'nullable|string',
            'importe' => 'required|numeric',
        ]);

        if ($operacion->cierre_caja_id) {
            $cierre = CierreCaja::find($operacion->cierre_caja_id);
            if ($cierre && !$cierre->fecha_cierre) {
                // Revert old values
                switch ($operacion->tipo) {
                    case 'ingreso':
                        $cierre->ingresos -= $operacion->importe;
                        break;
                    case 'gasto':
                        $cierre->egresos -= $operacion->importe;
                        break;
                    case 'aportacion':
                        $cierre->aportaciones -= $operacion->importe;
                        break;
                    case 'sustraccion':
                        $cierre->sustracciones -= $operacion->importe;
                        break;
                }

                // Add new values
                switch ($data['tipo']) {
                    case 'ingreso':
                        $cierre->ingresos += $data['importe'];
                        break;
                    case 'gasto':
                        $cierre->egresos += $data['importe'];
                        break;
                    case 'aportacion':
                        $cierre->aportaciones += $data['importe'];
                        break;
                    case 'sustraccion':
                        $cierre->sustracciones += $data['importe'];
                        break;
                }
                $cierre->save();
            }
        }

        $operacion->update($data);

        return response()->json(['success' => true, 'operacion' => $operacion]);
    }

    public function destroy($id)
    {
        $operacion = OperacionCaja::findOrFail($id);

        if ($operacion->cierre_caja_id) {
            $cierre = CierreCaja::find($operacion->cierre_caja_id);
            if ($cierre && !$cierre->fecha_cierre) {
                // Revert values
                switch ($operacion->tipo) {
                    case 'ingreso':
                        $cierre->ingresos -= $operacion->importe;
                        break;
                    case 'gasto':
                        $cierre->egresos -= $operacion->importe;
                        break;
                    case 'aportacion':
                        $cierre->aportaciones -= $operacion->importe;
                        break;
                    case 'sustraccion':
                        $cierre->sustracciones -= $operacion->importe;
                        break;
                }
                $cierre->save();
            }
        }

        $operacion->delete();

        return response()->json(['success' => true]);
    }
}
