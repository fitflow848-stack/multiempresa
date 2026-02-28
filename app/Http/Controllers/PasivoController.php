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
        $tipos = TipoPasivo::whereNotIn('nombre', ['Adelanto clientes', 'Adelanto de clientes', 'Adelantos personal'])
            ->orderBy('nombre')
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

            // Si es Adelanto de Cliente o Aporte, registrar en Caja
            $tipo = $pasivo->tipo->nombre;
            if (in_array(strtolower($tipo), ['adelanto de clientes', 'aporte'])) {
                $selectedCajaId = session('selected_caja_id');
                $cajaAbierta = null;

                if ($selectedCajaId) {
                    $cajaAbierta = CierreCaja::where('user_id', Auth::id())
                        ->where('caja_id', $selectedCajaId)
                        ->whereNull('fecha_cierre')
                        ->first();
                } else {
                    $cajaAbierta = CierreCaja::where('user_id', Auth::id())
                        ->whereNull('fecha_cierre')
                        ->first();
                }

                if (!$cajaAbierta) {
                    throw new \Exception('No se puede registrar este ' . $tipo . ' porque no tienes una caja abierta. Por favor, abre una caja antes de continuar.');
                }

                $cajaAbierta->ingresos = ($cajaAbierta->ingresos ?? 0) + $pasivo->monto;
                $cajaAbierta->save();

                OperacionCaja::create([
                    'cierre_caja_id' => $cajaAbierta->id,
                    'user_id' => Auth::id(),
                    'tipo' => 'ingreso',
                    'partida' => $tipo,
                    'concepto' => 'Registro de ' . $tipo . ': ' . $pasivo->nombre,
                    'importe' => $pasivo->monto,
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

            // Registrar en OperacionCaja como Egreso (Gasto) si hay caja abierta
            $selectedCajaId = session('selected_caja_id');
            $cajaAbierta = null;

            if ($selectedCajaId) {
                $cajaAbierta = CierreCaja::where('user_id', Auth::id())
                    ->where('caja_id', $selectedCajaId)
                    ->whereNull('fecha_cierre')
                    ->first();
            } else {
                $cajaAbierta = CierreCaja::where('user_id', Auth::id())
                    ->whereNull('fecha_cierre')
                    ->first();
            }

            if (!$cajaAbierta) {
                throw new \Exception('No se puede registrar el pago porque no tienes una caja abierta. Por favor, abre una caja antes de continuar.');
            }

            $cajaAbierta->egresos = ($cajaAbierta->egresos ?? 0) + $monto;
            $cajaAbierta->save();

            OperacionCaja::create([
                'cierre_caja_id' => $cajaAbierta->id,
                'user_id' => Auth::id(),
                'tipo' => 'gasto',
                'partida' => 'Pago Pasivo',
                'concepto' => 'Pago de ' . $pasivo->tipo->nombre . ': ' . $pasivo->nombre,
                'importe' => $monto,
            ]);

            DB::commit();

            return redirect()->route('pasivos.index')->with('success', 'Pago registrado correctamente.')->with('pago_id', $pago->id);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al registrar el pago: ' . $e->getMessage());
        }
    }

    public function ticketPago($id)
    {
        $pago = PasivoPago::with(['pasivo.tipo', 'user'])->findOrFail($id);
        $company = Company::first();

        $pdf = Pdf::loadView('pasivos.ticket', compact('pago', 'company'))
            ->setPaper([0, 0, 226, 600], 'portrait'); // Tamaño térmico aprox

        return $pdf->stream('ticket_pago_pasivo_' . $pago->id . '.pdf');
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
