<?php

namespace App\Http\Controllers;

use App\Models\Aporte;
use App\Models\TipoAporte;
use App\Models\OperacionCaja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AporteController extends Controller
{
    public function index(Request $request)
    {
        $tipos = TipoAporte::orderBy('nombre')->get();

        $query = Aporte::with('tipo')->orderBy('fecha_registro', 'desc');

        if ($request->filled('tipo_id')) {
            $query->where('tipo_aporte_id', $request->tipo_id);
        }
        if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecha_registro', '>=', $request->fecha_inicio);
        }
        if ($request->filled('fecha_fin')) {
            $query->whereDate('fecha_registro', '<=', $request->fecha_fin);
        }

        $aportes = $query->paginate(20);

        $aportes = $query->paginate(20);

        return view('aportes.index', compact('tipos', 'aportes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo_aporte_id' => 'required|exists:tipo_aportes,id',
            'nombre' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0',
            'fecha_registro' => 'required|date',
            'documento' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string',
            'metodo_pago' => 'required|in:caja,banco',
        ]);

        try {
            DB::beginTransaction();

            $metodoPago = $request->metodo_pago;

            if ($metodoPago === 'caja') {
                // Verificar que hay una caja abierta en sesión
                $cajaAbierta = getSelectedCaja();
                
                if (!$cajaAbierta) {
                    return redirect()->back()
                        ->with('error', 'No hay una caja abierta. Debe seleccionar y abrir una caja antes de registrar un aporte en efectivo.')
                        ->withInput();
                }
            }

            // Crear el registro contable del aporte
            $aporte = new Aporte($request->all());
            $aporte->user_id = Auth::id();
            $aporte->save();

            if ($metodoPago === 'caja') {
                // Ingreso a caja física
                $operacionCaja = new OperacionCaja([
                    'company_id' => auth()->user()->company_id,
                    'sucursal_id' => $cajaAbierta->sucursal_id,
                    'cierre_caja_id' => $cajaAbierta->id,
                    'user_id' => Auth::id(),
                    'tipo' => 'aportacion',
                    'partida' => 'Aporte - ' . $aporte->nombre,
                    'concepto' => $request->observaciones ?? 'Aporte registrado: ' . $aporte->nombre,
                    'importe' => $aporte->monto,
                    'es_efectivo' => true,
                    'metodo_pago' => 'Efectivo'
                ]);
                $operacionCaja->save();

                // Actualizar el saldo de aportaciones de la caja
                $cajaAbierta->aportaciones = ($cajaAbierta->aportaciones ?? 0) + $aporte->monto;
                $cajaAbierta->save();

                DB::commit();
                $cajaNombre = $cajaAbierta->caja->nombre ?? 'Caja';
                return redirect()->route('aportes.index')
                    ->with('success', "Aporte registrado correctamente. S/ " . number_format($aporte->monto, 2) . " agregado a {$cajaNombre}.");
            } else {
                // Ingreso a banco
                $banco = \App\Models\CuentaBancaria::where('company_id', Auth::user()->company_id)
                    ->where('is_active', true)->orderBy('id')->first();

                if (!$banco) {
                    DB::rollBack();
                    return redirect()->back()
                        ->with('error', 'No hay cuenta bancaria activa para registrar el aporte.')
                        ->withInput();
                }

                \App\Models\BancoMovimiento::create([
                    'cuenta_bancaria_id' => $banco->id,
                    'user_id' => Auth::id(),
                    'tipo' => 'ingreso',
                    'monto' => $aporte->monto,
                    'concepto' => 'Aporte: ' . $aporte->nombre,
                    'referencia' => $request->documento ?? null,
                    'fecha' => $request->fecha_registro,
                    'sucursal_id' => Auth::user()->branch_id,
                ]);
                $banco->increment('saldo_actual', $aporte->monto);

                DB::commit();
                return redirect()->route('aportes.index')
                    ->with('success', "Aporte registrado correctamente. S/ " . number_format($aporte->monto, 2) . " agregado a {$banco->banco_nombre}.");
            }
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al registrar el aporte: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $aporte = Aporte::findOrFail($id);
        $aporte->delete();

        return redirect()->route('aportes.index')->with('success', 'Aporte eliminado correctamente');
    }

    public function devolver(Request $request, $id)
    {
        $request->validate([
            'monto_devolucion' => 'required|numeric|min:0.01',
            'metodo_pago' => 'required|in:caja,banco',
        ]);

        $aporte = Aporte::findOrFail($id);
        $montoDevolucion = (float) $request->monto_devolucion;

        if ($montoDevolucion > $aporte->monto) {
            return redirect()->back()->with('error', 'El monto a devolver no puede ser mayor al monto del aporte.');
        }

        DB::beginTransaction();
        try {
            if ($request->metodo_pago === 'caja') {
                $cajaAbierta = getSelectedCaja();
                if (!$cajaAbierta) {
                    return redirect()->back()->with('error', 'No hay una caja abierta para registrar la devolución.');
                }

                // Restar de caja (es un egreso/sustracción)
                $cajaAbierta->sustracciones = floatval($cajaAbierta->sustracciones ?? 0) + $montoDevolucion;
                $cajaAbierta->save();

                OperacionCaja::create([
                    'company_id' => Auth::user()->company_id,
                    'sucursal_id' => $cajaAbierta->sucursal_id,
                    'cierre_caja_id' => $cajaAbierta->id,
                    'user_id' => Auth::id(),
                    'tipo' => 'sustraccion',
                    'partida' => 'Devolución Aporte',
                    'concepto' => 'Devolución de aporte: ' . $aporte->nombre . ' (S/ ' . number_format($montoDevolucion, 2) . ')',
                    'importe' => $montoDevolucion,
                    'es_efectivo' => true,
                    'metodo_pago' => 'Efectivo',
                ]);
            } else {
                // Restar de banco
                $banco = \App\Models\CuentaBancaria::where('company_id', Auth::user()->company_id)
                    ->where('is_active', true)->orderBy('id')->first();
                if (!$banco) {
                    return redirect()->back()->with('error', 'No hay cuenta bancaria activa para registrar la devolución.');
                }

                \App\Models\BancoMovimiento::create([
                    'cuenta_bancaria_id' => $banco->id,
                    'user_id' => Auth::id(),
                    'tipo' => 'egreso',
                    'monto' => $montoDevolucion,
                    'concepto' => 'Devolución de aporte: ' . $aporte->nombre,
                    'fecha' => now()->toDateString(),
                    'sucursal_id' => Auth::user()->branch_id,
                ]);
                $banco->decrement('saldo_actual', $montoDevolucion);
            }

            // Reducir el monto del aporte (patrimonio)
            $aporte->monto = $aporte->monto - $montoDevolucion;
            $aporte->save();

            DB::commit();
            return redirect()->route('aportes.index')->with('success', 'Devolución de aporte registrada. Se devolvió S/ ' . number_format($montoDevolucion, 2));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al registrar devolución: ' . $e->getMessage());
        }
    }

    public function storeTipo(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|unique:tipo_aportes,nombre|max:255',
            'descripcion' => 'nullable|string'
        ]);

        $tipo = TipoAporte::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tipo de aporte creado exitosamente',
                'tipo' => $tipo
            ]);
        }

        return redirect()->route('aportes.index')->with('success', 'Tipo de aporte creado');
    }
}
