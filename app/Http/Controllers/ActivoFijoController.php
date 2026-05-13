<?php

namespace App\Http\Controllers;

use App\Models\ActivoFijo;
use App\Models\TipoActivo;
use Illuminate\Http\Request;

class ActivoFijoController extends Controller
{
    public function index(Request $request)
    {
        // Asegurar que la empresa tenga los tipos por defecto
        \App\Helpers\AccountingHelper::ensureDefaults(auth()->user()->company_id);

        $tipos = TipoActivo::orderBy('nombre')->get();

        $query = ActivoFijo::with('tipo')->orderBy('fecha_adquisicion', 'desc');

        if ($request->filled('tipo_id')) {
            $query->where('tipo_activo_id', $request->tipo_id);
        }
        if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecha_adquisicion', '>=', $request->fecha_inicio);
        }
        if ($request->filled('fecha_fin')) {
            $query->whereDate('fecha_adquisicion', '<=', $request->fecha_fin);
        }

        $activos = $query->paginate(20);

        return view('activos.index', compact('tipos', 'activos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo_activo_id' => 'required|exists:tipo_activos,id',
            'nombre' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0',
            'fecha_adquisicion' => 'required|date',
            'documento' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string'
        ]);

        ActivoFijo::create($request->all());

        return redirect()->route('activos.index')->with('success', 'Activo registrado correctamente');
    }

    public function storeTipo(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|unique:tipo_activos,nombre|max:255',
            'descripcion' => 'nullable|string'
        ]);

        $tipo = TipoActivo::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tipo de activo creado exitosamente',
                'tipo' => $tipo
            ]);
        }

        return redirect()->route('activos.index')->with('success', 'Tipo de activo creado');
    }

    public function destroy($id)
    {
        $activo = ActivoFijo::findOrFail($id);
        $activo->delete();

        return redirect()->route('activos.index')->with('success', 'Activo eliminado correctamente');
    }

    /**
     * Registrar pago de un activo no corriente - afecta caja o banco.
     */
    public function pagar(Request $request, $id)
    {
        $request->validate([
            'metodo_pago' => 'required|string|in:Efectivo,Transferencia',
        ]);

        $activo = ActivoFijo::findOrFail($id);
        $user = auth()->user();
        $monto = (float) $activo->monto;
        $metodoPago = $request->metodo_pago;
        $esEfectivo = strtolower($metodoPago) === 'efectivo';

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            if ($esEfectivo) {
                $cajaAbierta = requireSelectedCaja('registrar el pago del activo');

                $cajaAbierta->sustracciones = floatval($cajaAbierta->sustracciones ?? 0) + $monto;
                $cajaAbierta->save();

                \App\Models\OperacionCaja::create([
                    'cierre_caja_id' => $cajaAbierta->id,
                    'user_id' => $user->id,
                    'tipo' => 'sustraccion',
                    'partida' => 'Pago Activo No Corriente',
                    'concepto' => $activo->nombre,
                    'importe' => $monto,
                    'metodo_pago' => $metodoPago,
                    'es_efectivo' => 1,
                ]);

                $activo->update([
                    'is_paid' => true,
                    'metodo_pago' => $metodoPago,
                    'cierre_caja_id' => $cajaAbierta->id,
                ]);
            } else {
                $banco = \App\Models\CuentaBancaria::preferidaParaUsuario();
                if (!$banco) {
                    throw new \Exception('No hay cuenta bancaria activa para registrar el pago.');
                }

                \App\Models\BancoMovimiento::create([
                    'cuenta_bancaria_id' => $banco->id,
                    'user_id' => $user->id,
                    'tipo' => 'egreso',
                    'monto' => $monto,
                    'concepto' => 'Pago Activo No Corriente: ' . $activo->nombre,
                    'referencia' => $activo->documento,
                    'fecha' => now()->toDateString(),
                    'sucursal_id' => $user->branch_id,
                ]);
                $banco->decrement('saldo_actual', $monto);

                $activo->update([
                    'is_paid' => true,
                    'metodo_pago' => $metodoPago,
                ]);
            }

            \Illuminate\Support\Facades\DB::commit();
            return redirect()->route('activos.index')->with('success', 'Pago registrado correctamente.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return redirect()->back()->with('error', 'Error al registrar pago: ' . $e->getMessage());
        }
    }
}
