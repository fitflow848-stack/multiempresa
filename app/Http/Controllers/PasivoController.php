<?php

namespace App\Http\Controllers;

use App\Models\Pasivo;
use App\Models\TipoPasivo;
use App\Models\PasivoPago;
use App\Models\Company;
use App\Models\CierreCaja;
use App\Models\OperacionCaja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class PasivoController extends Controller
{
    public function index(Request $request)
    {
        // Asegurar que la empresa tenga los tipos por defecto
        \App\Helpers\AccountingHelper::ensureDefaults(auth()->user()->company_id);

        $tipos = TipoPasivo::orderBy('nombre')
            ->get();

        $query = Pasivo::with(['tipo', 'pagos'])->orderBy('fecha_registro', 'desc');

        if ($request->filled('tipo_id')) {
            $query->where('tipo_pasivo_id', $request->tipo_id);
        }
        if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecha_registro', '>=', $request->fecha_inicio);
        }
        if ($request->filled('fecha_fin')) {
            $query->whereDate('fecha_registro', '<=', $request->fecha_fin);
        }

        $pasivos = $query->paginate(20);

        return view('pasivos.index', compact('tipos', 'pasivos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo_pasivo_id' => 'required|exists:tipo_pasivos,id',
            'nombre' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0',
            'fecha_registro' => 'required|date',
            'documento' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $pasivo = Pasivo::create($request->all());

            // Si es Adelanto de Cliente, registrar en Caja (Aporte ya no afecta caja directamente)
            $tipo = $pasivo->tipo->nombre;
            if (in_array(strtolower($tipo), ['adelanto de clientes'])) {
                $metodoPago = $request->input('metodo_pago', 'Efectivo');
                $esEfectivo = (strtolower($metodoPago) === 'efectivo' || $metodoPago === '1' || $metodoPago === 1) ? 1 : 0;

                $cajaAbierta = requireSelectedCaja('registrar este ' . $tipo);

                if ($esEfectivo) {
                    $cajaAbierta->ingresos = ($cajaAbierta->ingresos ?? 0) + $pasivo->monto;
                    $cajaAbierta->save();
                }

                OperacionCaja::create([
                    'cierre_caja_id' => $cajaAbierta->id,
                    'user_id' => Auth::id(),
                    'tipo' => 'ingreso',
                    'partida' => $tipo,
                    'concepto' => 'Registro de ' . $tipo . ': ' . $pasivo->nombre,
                    'importe' => $pasivo->monto,
                    'metodo_pago' => $metodoPago,
                    'es_efectivo' => $esEfectivo,
                ]);
            }

            DB::commit();
            return redirect()->route('pasivos.index')->with('success', 'Registro creado correctamente' . ($cajaAbierta ?? false ? ' y reflejado en caja.' : '.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al guardar: ' . $e->getMessage());
        }
    }

    public function storeTipo(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|unique:tipo_pasivos,nombre|max:255',
            'descripcion' => 'nullable|string'
        ]);

        $tipo = TipoPasivo::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tipo de pasivo creado exitosamente',
                'tipo' => $tipo
            ]);
        }

        return redirect()->route('pasivos.index')->with('success', 'Tipo de pasivo creado');
    }

    public function destroy($id)
    {
        $pasivo = Pasivo::findOrFail($id);
        $pasivo->delete();

        return redirect()->route('pasivos.index')->with('success', 'Pasivo eliminado correctamente');
    }

    public function convertirAporte($id)
    {
        try {
            $pasivo = Pasivo::findOrFail($id);

            // Buscar el ID del tipo "Aporte"
            $tipoAporte = TipoPasivo::where('nombre', 'Aporte')->first();

            if (!$tipoAporte) {
                return redirect()->route('pasivos.index')->with('error', 'No se encontró el tipo de pasivo "Aporte".');
            }

            $pasivo->update([
                'tipo_pasivo_id' => $tipoAporte->id
            ]);

            return redirect()->route('pasivos.index')->with('success', 'El registro se ha cambiado a tipo APORTE correctamente.');
        } catch (\Exception $e) {
            return redirect()->route('pasivos.index')->with('error', 'Error al cambiar tipo: ' . $e->getMessage());
        }
    }

    public function registrarPago(Request $request, $id)
    {
        $request->validate([
            'monto' => 'required|numeric|min:0.01',
            'metodo_pago' => 'required|string',
            'fecha_pago' => 'required|date',
            'documento_pago' => 'nullable|string',
            'observaciones' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $pasivo = Pasivo::findOrFail($id);
            $monto = $request->monto;

            if ($monto > $pasivo->saldo) {
                return back()->with('error', 'El monto a pagar excede el saldo pendiente (S/ ' . number_format($pasivo->saldo, 2) . ')');
            }

            // Crear el pago
            $pago = PasivoPago::create([
                'pasivo_id' => $pasivo->id,
                'user_id' => Auth::id(),
                'monto' => $monto,
                'fecha_pago' => $request->fecha_pago,
                'metodo_pago' => $request->metodo_pago,
                'documento_pago' => $request->documento_pago,
                'observaciones' => $request->observaciones
            ]);

            // Actualizar el pasivo
            $pasivo->monto_pagado += $monto;

            if ($pasivo->monto_pagado >= $pasivo->monto) {
                $pasivo->estado = 'pagado';
            } else {
                $pasivo->estado = 'parcial';
            }
            $pasivo->save();

            // Registrar en OperacionCaja
            if (true) {
                $cajaAbierta = requireSelectedCaja('registrar el pago');

                $metodoPago = $request->metodo_pago ?? 'Efectivo';
                $esEfectivo = (strtolower($metodoPago) === 'efectivo' || $metodoPago === '1' || $metodoPago === 1) ? 1 : 0;

                if ($pasivo->tipo->nombre === 'Adelantos personal') {
                    if ($esEfectivo) $cajaAbierta->ingresos = ($cajaAbierta->ingresos ?? 0) + $monto;
                    $tipoOp = 'ingreso';
                    $partida = 'Liquidación Adelanto';
                } elseif (in_array(strtolower($pasivo->tipo->nombre), ['adelanto clientes', 'adelanto de clientes'])) {
                    if ($esEfectivo) $cajaAbierta->sustracciones = ($cajaAbierta->sustracciones ?? 0) + $monto;
                    $tipoOp = 'sustraccion';
                    $partida = 'Entrega Producto (Adelanto)';
                } elseif (in_array(strtolower($pasivo->tipo->nombre), ['compras a crédito', 'compras a credito'])) {
                    if ($esEfectivo) $cajaAbierta->sustracciones = ($cajaAbierta->sustracciones ?? 0) + $monto;
                    $tipoOp = 'sustraccion';
                    $partida = 'Pago Compra Crédito';
                } elseif (in_array(strtolower($pasivo->tipo->nombre), ['aporte', 'aportes'])) {
                    if ($esEfectivo) $cajaAbierta->ingresos = ($cajaAbierta->ingresos ?? 0) + $monto;
                    $tipoOp = 'aporte';
                    $partida = 'Aporte de Capital';
                } else {
                    if ($esEfectivo) $cajaAbierta->egresos = ($cajaAbierta->egresos ?? 0) + $monto;
                    $tipoOp = 'gasto';
                    $partida = 'Pago Pasivo';
                }
                
                if ($esEfectivo) {
                    $cajaAbierta->save();
                }

                OperacionCaja::create([
                    'cierre_caja_id' => $cajaAbierta->id,
                    'user_id' => Auth::id(),
                    'tipo' => $tipoOp,
                    'partida' => $partida,
                    'concepto' => ($tipoOp === 'ingreso' ? 'Recepción de pago/saldado: ' : 'Pago de ') . $pasivo->tipo->nombre . ': ' . $pasivo->nombre,
                    'importe' => $monto,
                    'metodo_pago' => $metodoPago,
                    'es_efectivo' => $esEfectivo,
                ]);
            }

            DB::commit();

            return back()->with('success', 'Pago registrado correctamente.')->with('pago_id', $pago->id);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al registrar el pago: ' . $e->getMessage());
        }
    }

    public function ticketPago($id)
    {
        $pago = PasivoPago::with(['pasivo.tipo', 'pasivo.company', 'user'])->findOrFail($id);
        $company = $pago->pasivo->company ?? Company::first();

        $pdf = Pdf::loadView('pasivos.ticket', compact('pago', 'company'))
            ->setPaper([0, 0, 226, 600], 'portrait'); // Tamaño térmico aprox

        return $pdf->stream('ticket_pago_pasivo_' . $pago->id . '.pdf');
    }

    public function ticketRegistro($id)
    {
        $pasivo = Pasivo::with(['tipo', 'sucursal', 'company'])->findOrFail($id);
        $company = $pasivo->company ?? Company::first();

        $pdf = Pdf::loadView('pasivos.ticket_registro', compact('pasivo', 'company'))
            ->setPaper([0, 0, 226, 600], 'portrait');

        return $pdf->stream('ticket_registro_pasivo_' . $pasivo->id . '.pdf');
    }

    public function edit($id)
    {
        $pasivo = Pasivo::with('tipo')->findOrFail($id);
        return response()->json($pasivo);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tipo_pasivo_id' => 'required|exists:tipo_pasivos,id',
            'nombre' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0',
            'fecha_registro' => 'required|date',
            'documento' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string'
        ]);

        $pasivo = Pasivo::findOrFail($id);
        $pasivo->update($request->all());

        return redirect()->route('pasivos.index')->with('success', 'Pasivo actualizado correctamente');
    }
}
