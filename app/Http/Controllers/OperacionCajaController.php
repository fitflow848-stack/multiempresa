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
            'metodo_pago' => 'nullable|string|max:50',
            'importe' => 'required|numeric',
        ]);

        $data['user_id'] = Auth::id();
        $data['metodo_pago'] = $data['metodo_pago'] ?? 'Efectivo';
        $data['es_efectivo'] = ($data['metodo_pago'] === 'Efectivo') ? 1 : 0;

        $operacion = OperacionCaja::create($data);

        if (!empty($data['cierre_caja_id']) && $data['es_efectivo']) {
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

        if ($operacion->partida === 'Cobro Deuda') {
            return response()->json([
                'success' => false,
                'message' => 'No se puede editar una operación que provenga de un cobro de deuda.'
            ], 400);
        }

        $data = $request->validate([
            'tipo' => 'required|in:aportacion,sustraccion,ingreso,gasto',
            'partida' => 'nullable|string|max:255',
            'concepto' => 'nullable|string',
            'metodo_pago' => 'nullable|string|max:50',
            'importe' => 'required|numeric',
        ]);

        $data['metodo_pago'] = $data['metodo_pago'] ?? 'Efectivo';
        $data['es_efectivo'] = ($data['metodo_pago'] === 'Efectivo') ? 1 : 0;

        if ($operacion->cierre_caja_id) {
            $cierre = CierreCaja::find($operacion->cierre_caja_id);
            if ($cierre && !$cierre->fecha_cierre) {
                // Revert old values ONLY if it was cash
                if ($operacion->es_efectivo) {
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
                }

                // Add new values ONLY if it is cash
                if ($data['es_efectivo']) {
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

        if ($operacion->partida === 'Cobro Deuda') {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar una operación que provenga de un cobro de deuda.'
            ], 400);
        }

        if ($operacion->cierre_caja_id) {
            $cierre = CierreCaja::find($operacion->cierre_caja_id);
            if ($cierre && !$cierre->fecha_cierre) {
                // Revert values ONLY if it was cash
                if ($operacion->es_efectivo) {
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
                }
                $cierre->save();
            }
        }

        $operacion->delete();

        return response()->json(['success' => true]);
    }
}
