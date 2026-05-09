<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OperacionCaja;
use App\Models\CierreCaja;
use App\Models\Caja;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OperacionCajaController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'cierre_caja_id' => 'nullable|exists:cierre_cajas,id',
            'tipo' => 'required|in:aportacion,sustraccion,ingreso,gasto,transferencia_boveda,pase_banco',
            'partida' => 'nullable|string|max:255',
            'concepto' => 'nullable|string',
            'metodo_pago' => 'nullable|string|max:50',
            'importe' => 'required|numeric',
        ]);

        $data['user_id'] = Auth::id();
        $data['metodo_pago'] = $data['metodo_pago'] ?? 'Efectivo';
        $data['es_efectivo'] = ($data['metodo_pago'] === 'Efectivo') ? 1 : 0;

        $isTransferenciaBoveda = $data['tipo'] === 'transferencia_boveda';

        if ($isTransferenciaBoveda) {
            $user = Auth::user();
            $bovedaAbierta = CierreCaja::whereNull('fecha_cierre')
                ->whereHas('caja', function ($q) {
                    $q->where('is_boveda', true);
                })
                ->where('id_empresa', $user->company_id)
                ->first();

            if (!$bovedaAbierta) {
                return response()->json(['success' => false, 'message' => 'No hay ninguna Tesorería / Bóveda con sesión abierta actualmente.'], 400);
            }

            $data['tipo'] = 'sustraccion';
            $data['partida'] = 'Transferencia a Tesorería';
            $data['concepto'] = 'Envío de fondos: ' . ($data['concepto'] ?? '');
        }

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

        if ($isTransferenciaBoveda && isset($bovedaAbierta)) {
            OperacionCaja::create([
                'cierre_caja_id' => $bovedaAbierta->id,
                'user_id' => $data['user_id'],
                'tipo' => 'ingreso',
                'partida' => 'Recepción de Caja',
                'concepto' => 'Recibido de caja #' . $data['cierre_caja_id'] . ': ' . ($request->concepto ?? ''),
                'importe' => $data['importe'],
                'es_efectivo' => $data['es_efectivo'],
                'metodo_pago' => $data['metodo_pago'],
            ]);

            if ($data['es_efectivo']) {
                $bovedaAbierta->ingresos = ($bovedaAbierta->ingresos ?? 0) + $data['importe'];
                $bovedaAbierta->save();
            }
        }

        return response()->json(['success' => true, 'operacion' => $operacion]);
    }

    /**
     * PASE CAJA → BÓVEDA
     * Cajero envía exceso de efectivo a la bóveda. Admin recepciona.
     */
    public function transferenciaCajaABoveda(Request $request)
    {
        $data = $request->validate([
            'cierre_caja_id' => 'required|exists:cierre_cajas,id',
            'importe'        => 'required|numeric|min:0.01',
            'concepto'       => 'nullable|string|max:255',
        ]);

        $user = Auth::user();

        $cierreOrigen = CierreCaja::where('id', $data['cierre_caja_id'])
            ->where('id_empresa', $user->company_id)
            ->whereNull('fecha_cierre')
            ->first();

        if (!$cierreOrigen) {
            return response()->json(['success' => false, 'message' => 'La caja de origen no está activa o no es válida.'], 400);
        }

        $bovedaAbierta = CierreCaja::whereNull('fecha_cierre')
            ->whereHas('caja', fn($q) => $q->where('is_boveda', true))
            ->where('id_empresa', $user->company_id)
            ->first();

        if (!$bovedaAbierta) {
            return response()->json(['success' => false, 'message' => 'No hay ninguna Bóveda con sesión abierta. Solicite al administrador que abra la bóveda primero.'], 422);
        }

        $concepto = $data['concepto'] ?: 'Exceso de caja transferido';
        $nombreCaja = optional($cierreOrigen->caja)->nombre ?? 'Caja #' . $cierreOrigen->id;
        $nombreBoveda = optional($bovedaAbierta->caja)->nombre ?? 'Bóveda';

        DB::transaction(function () use ($data, $user, $cierreOrigen, $bovedaAbierta, $concepto, $nombreCaja) {
            OperacionCaja::create([
                'cierre_caja_id' => $cierreOrigen->id,
                'user_id'        => $user->id,
                'tipo'           => 'sustraccion',
                'partida'        => 'Pase a Bóveda',
                'concepto'       => 'Pase a Bóveda: ' . $concepto,
                'importe'        => $data['importe'],
                'es_efectivo'    => 1,
                'metodo_pago'    => 'Efectivo',
            ]);
            $cierreOrigen->sustracciones = ($cierreOrigen->sustracciones ?? 0) + $data['importe'];
            $cierreOrigen->save();

            OperacionCaja::create([
                'cierre_caja_id' => $bovedaAbierta->id,
                'user_id'        => $user->id,
                'tipo'           => 'ingreso',
                'partida'        => 'Recepción desde Caja',
                'concepto'       => 'Recibido de ' . $nombreCaja . ': ' . $concepto,
                'importe'        => $data['importe'],
                'es_efectivo'    => 1,
                'metodo_pago'    => 'Efectivo',
            ]);
            $bovedaAbierta->ingresos = ($bovedaAbierta->ingresos ?? 0) + $data['importe'];
            $bovedaAbierta->save();
        });

        return response()->json([
            'success' => true,
            'message' => 'Pase a bóveda registrado correctamente.',
            'ticket_data' => [
                'tipo'             => 'PASE CAJA → BÓVEDA',
                'origen'           => $nombreCaja,
                'destino'          => $nombreBoveda,
                'importe'          => $data['importe'],
                'concepto'         => $concepto,
                'usuario'          => $user->name,
                'fecha'            => now()->format('d/m/Y H:i:s'),
                'id_origen'        => $cierreOrigen->id,
                'id_destino'       => $bovedaAbierta->id,
            ]
        ]);
    }

    /**
     * BÓVEDA → CAJA (Petición/Recepción desde la Caja)
     * El cajero solicita/registra que está recibiendo efectivo de la bóveda
     */
    public function transferenciaCajaDesdeBoveda(Request $request)
    {
        $data = $request->validate([
            'cierre_caja_id' => 'required|exists:cierre_cajas,id',
            'importe'        => 'required|numeric|min:0.01',
            'concepto'       => 'nullable|string|max:255',
        ]);

        $user = Auth::user();
        
        $cierreOrigen = CierreCaja::with('caja')->find($data['cierre_caja_id']);
        
        // Buscamos bóveda abierta de la empresa
        $bovedaAbierta = CierreCaja::where('id_empresa', $user->company_id)
            ->whereNull('fecha_cierre')
            ->whereHas('caja', fn($q) => $q->where('is_boveda', true))
            ->first();

        if (!$bovedaAbierta) {
            return response()->json(['success' => false, 'message' => 'No hay caja en tesorería (bóveda) abierta.'], 400);
        }

        $nombreCaja = optional($cierreOrigen->caja)->nombre ?? 'Caja';
        $nombreBoveda = optional($bovedaAbierta->caja)->nombre ?? 'Bóveda';
        $concepto = $data['concepto'] ?: 'Transferencia recibida de bóveda';

        DB::transaction(function () use ($data, $user, $cierreOrigen, $bovedaAbierta, $nombreCaja, $concepto) {
            OperacionCaja::create([
                'cierre_caja_id' => $bovedaAbierta->id,
                'user_id'        => $user->id,
                'tipo'           => 'sustraccion',
                'partida'        => 'Pase a Caja',
                'concepto'       => 'Pase a ' . $nombreCaja . ': ' . $concepto,
                'importe'        => $data['importe'],
                'es_efectivo'    => 1,
                'metodo_pago'    => 'Efectivo',
            ]);
            $bovedaAbierta->sustracciones = ($bovedaAbierta->sustracciones ?? 0) + $data['importe'];
            $bovedaAbierta->save();

            OperacionCaja::create([
                'cierre_caja_id' => $cierreOrigen->id,
                'user_id'        => $user->id,
                'tipo'           => 'ingreso',
                'partida'        => 'Recibido de Bóveda',
                'concepto'       => $concepto,
                'importe'        => $data['importe'],
                'es_efectivo'    => 1,
                'metodo_pago'    => 'Efectivo',
            ]);
            $cierreOrigen->ingresos = ($cierreOrigen->ingresos ?? 0) + $data['importe'];
            $cierreOrigen->save();
        });

        return response()->json([
            'success' => true,
            'message' => 'Recepción desde bóveda registrada correctamente.',
            'ticket_data' => [
                'tipo'             => 'PASE BÓVEDA → CAJA',
                'origen'           => $nombreBoveda,
                'destino'          => $nombreCaja,
                'importe'          => $data['importe'],
                'concepto'         => $concepto,
                'usuario'          => $user->name,
                'fecha'            => now()->format('d/m/Y H:i:s'),
                'id_origen'        => $bovedaAbierta->id,
                'id_destino'       => $cierreOrigen->id,
            ]
        ]);
    }

    /**
     * PASE BÓVEDA → CAJA
     * Solo admin/supervisor. Transfiere a una caja específica con sesión abierta.
     */
    public function transferenciaBovedaACaja(Request $request)
    {
        $data = $request->validate([
            'cierre_boveda_id' => 'required|exists:cierre_cajas,id',
            'caja_destino_id'  => 'required|exists:cajas,id',
            'importe'          => 'required|numeric|min:0.01',
            'concepto'         => 'nullable|string|max:255',
        ]);

        $user = Auth::user();

        if (!$user->hasAnyRole(['super_admin', 'admin_empresa', 'supervisor'])) {
            return response()->json(['success' => false, 'message' => 'No tiene permisos para realizar transferencias desde la Bóveda.'], 403);
        }

        $cierreBoveda = CierreCaja::where('id', $data['cierre_boveda_id'])
            ->where('id_empresa', $user->company_id)
            ->whereNull('fecha_cierre')
            ->whereHas('caja', fn($q) => $q->where('is_boveda', true))
            ->first();

        if (!$cierreBoveda) {
            return response()->json(['success' => false, 'message' => 'La bóveda especificada no está activa.'], 400);
        }

        $cierreDestino = CierreCaja::where('caja_id', $data['caja_destino_id'])
            ->where('id_empresa', $user->company_id)
            ->whereNull('fecha_cierre')
            ->first();

        if (!$cierreDestino) {
            return response()->json(['success' => false, 'message' => 'La caja destino no tiene una sesión abierta actualmente.'], 422);
        }

        $cajaDestino = Caja::withoutGlobalScopes()->find($data['caja_destino_id']);
        $nombreBoveda = optional($cierreBoveda->caja)->nombre ?? 'Bóveda';
        $nombreCajaDestino = optional($cajaDestino)->nombre ?? 'Caja destino';
        $concepto = $data['concepto'] ?: 'Reposición de efectivo desde bóveda';

        DB::transaction(function () use ($data, $user, $cierreBoveda, $cierreDestino, $nombreCajaDestino, $concepto) {
            OperacionCaja::create([
                'cierre_caja_id' => $cierreBoveda->id,
                'user_id'        => $user->id,
                'tipo'           => 'sustraccion',
                'partida'        => 'Pase a Caja',
                'concepto'       => 'Enviado a ' . $nombreCajaDestino . ': ' . $concepto,
                'importe'        => $data['importe'],
                'es_efectivo'    => 1,
                'metodo_pago'    => 'Efectivo',
            ]);
            $cierreBoveda->sustracciones = ($cierreBoveda->sustracciones ?? 0) + $data['importe'];
            $cierreBoveda->save();

            OperacionCaja::create([
                'cierre_caja_id' => $cierreDestino->id,
                'user_id'        => $user->id,
                'tipo'           => 'ingreso',
                'partida'        => 'Recepción desde Bóveda',
                'concepto'       => 'Recibido de Bóveda: ' . $concepto,
                'importe'        => $data['importe'],
                'es_efectivo'    => 1,
                'metodo_pago'    => 'Efectivo',
            ]);
            $cierreDestino->ingresos = ($cierreDestino->ingresos ?? 0) + $data['importe'];
            $cierreDestino->save();
        });

        return response()->json([
            'success' => true,
            'message' => 'Transferencia desde bóveda registrada correctamente.',
            'ticket_data' => [
                'tipo'       => 'PASE BÓVEDA → CAJA',
                'origen'     => $nombreBoveda,
                'destino'    => $nombreCajaDestino,
                'importe'    => $data['importe'],
                'concepto'   => $concepto,
                'usuario'    => $user->name,
                'fecha'      => now()->format('d/m/Y H:i:s'),
                'id_origen'  => $cierreBoveda->id,
                'id_destino' => $cierreDestino->id,
            ]
        ]);
    }

    /**
     * Listar cajas abiertas disponibles para el pase bóveda→caja
     */
    public function cajasAbiertas(Request $request)
    {
        $user = Auth::user();
        $cajas = CierreCaja::whereNull('fecha_cierre')
            ->where('id_empresa', $user->company_id)
            ->whereHas('caja', fn($q) => $q->where('is_boveda', false))
            ->with('caja:id,nombre')
            ->get()
            ->map(fn($c) => [
                'id'     => $c->caja_id,
                'nombre' => optional($c->caja)->nombre . ' (sesión #' . $c->id . ')',
                'cierre_id' => $c->id,
            ]);

        return response()->json($cajas);
    }

    /**
     * Ticket imprimible para transferencias (GET con datos via query string)
     */
    public function ticketTransferencia(Request $request)
    {
        return view('cierres.ticket-transferencia', [
            'tipo'      => $request->tipo,
            'origen'    => $request->origen,
            'destino'   => $request->destino,
            'importe'   => $request->importe,
            'concepto'  => $request->concepto,
            'usuario'   => $request->usuario,
            'fecha'     => $request->fecha,
            'id_origen' => $request->id_origen,
            'id_destino'=> $request->id_destino,
        ]);
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('cajas.ajustar')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para editar operaciones de caja.'], 403);
        }

        $operacion = OperacionCaja::findOrFail($id);

        if ($operacion->partida === 'Cobro Deuda') {
            return response()->json(['success' => false, 'message' => 'No se puede editar una operación que provenga de un cobro de deuda.'], 400);
        }

        $data = $request->validate([
            'tipo'       => 'required|in:aportacion,sustraccion,ingreso,gasto',
            'partida'    => 'nullable|string|max:255',
            'concepto'   => 'nullable|string',
            'metodo_pago'=> 'nullable|string|max:50',
            'importe'    => 'required|numeric',
        ]);

        $data['metodo_pago'] = $data['metodo_pago'] ?? 'Efectivo';
        $data['es_efectivo'] = ($data['metodo_pago'] === 'Efectivo') ? 1 : 0;

        if ($operacion->cierre_caja_id) {
            $cierre = CierreCaja::find($operacion->cierre_caja_id);
            if ($cierre && !$cierre->fecha_cierre) {
                if ($operacion->es_efectivo) {
                    switch ($operacion->tipo) {
                        case 'ingreso':    $cierre->ingresos    -= $operacion->importe; break;
                        case 'gasto':      $cierre->egresos     -= $operacion->importe; break;
                        case 'aportacion': $cierre->aportaciones-= $operacion->importe; break;
                        case 'sustraccion':$cierre->sustracciones-=$operacion->importe; break;
                    }
                }
                if ($data['es_efectivo']) {
                    switch ($data['tipo']) {
                        case 'ingreso':    $cierre->ingresos    += $data['importe']; break;
                        case 'gasto':      $cierre->egresos     += $data['importe']; break;
                        case 'aportacion': $cierre->aportaciones+= $data['importe']; break;
                        case 'sustraccion':$cierre->sustracciones+=$data['importe']; break;
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
            return response()->json(['success' => false, 'message' => 'No se puede eliminar una operación que provenga de un cobro de deuda.'], 400);
        }

        if ($operacion->cierre_caja_id) {
            $cierre = CierreCaja::find($operacion->cierre_caja_id);
            if ($cierre && !$cierre->fecha_cierre && $operacion->es_efectivo) {
                switch ($operacion->tipo) {
                    case 'ingreso':    $cierre->ingresos    -= $operacion->importe; break;
                    case 'gasto':      $cierre->egresos     -= $operacion->importe; break;
                    case 'aportacion': $cierre->aportaciones-= $operacion->importe; break;
                    case 'sustraccion':$cierre->sustracciones-=$operacion->importe; break;
                }
                $cierre->save();
            }
        }

        $operacion->delete();
        return response()->json(['success' => true]);
    }
}
