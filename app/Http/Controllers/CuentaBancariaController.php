<?php

namespace App\Http\Controllers;

use App\Models\CuentaBancaria;
use App\Models\BancoMovimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CuentaBancariaController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $cuentas = CuentaBancaria::where('company_id', $user->company_id)->get();

        return view('bancos.index', compact('cuentas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'banco_nombre' => 'required|string',
            'numero_cuenta' => 'required|string',
            'moneda' => 'required|string',
            'saldo_inicial' => 'required|numeric',
        ]);

        $user = Auth::user();
        
        CuentaBancaria::create([
            'company_id' => $user->company_id,
            'sucursal_id' => $user->branch_id,
            'banco_nombre' => $request->banco_nombre,
            'tipo_cuenta' => $request->tipo_cuenta,
            'numero_cuenta' => $request->numero_cuenta,
            'cci' => $request->cci,
            'moneda' => $request->moneda,
            'saldo_inicial' => $request->saldo_inicial,
            'saldo_actual' => $request->saldo_inicial,
        ]);

        return redirect()->route('bancos.index')->with('success', 'Cuenta bancaria registrada correctamente.');
    }

    public function show(Request $request, CuentaBancaria $banco)
    {
        $this->authorizeOwner($banco);
        
        $user = Auth::user();
        $sucursales = DB::table('sucursales')->where('company_id', $user->company_id)->get();
        
        $query = $banco->movimientos()->with('sucursal');
        
        if ($request->filled('fecha_desde')) {
            $query->where('fecha', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->where('fecha', '<=', $request->fecha_hasta);
        }
        if ($request->filled('sucursal_id')) {
            $query->where('sucursal_id', $request->sucursal_id);
        }
        
        $movimientos = $query->latest()->paginate(30)->appends($request->query());
        $fecha_desde = $request->fecha_desde;
        $fecha_hasta = $request->fecha_hasta;
        $sucursal_id = $request->sucursal_id;
        
        return view('bancos.show', compact('banco', 'movimientos', 'fecha_desde', 'fecha_hasta', 'sucursales', 'sucursal_id'));
    }

    public function update(Request $request, CuentaBancaria $banco)
    {
        $this->authorizeOwner($banco);

        $request->validate([
            'banco_nombre' => 'required|string',
            'numero_cuenta' => 'required|string',
        ]);

        $banco->update($request->only(['banco_nombre', 'tipo_cuenta', 'numero_cuenta', 'cci', 'is_active']));

        return redirect()->route('bancos.index')->with('success', 'Cuenta bancaria actualizada.');
    }

    public function storeMovimiento(Request $request, CuentaBancaria $banco)
    {
        $this->authorizeOwner($banco);

        $request->validate([
            'tipo' => 'required|in:ingreso,egreso',
            'monto' => 'required|numeric|min:0.01',
            'concepto' => 'required|string',
            'fecha' => 'required|date',
        ]);

        DB::beginTransaction();
        try {
            BancoMovimiento::create([
                'cuenta_bancaria_id' => $banco->id,
                'user_id' => Auth::id(),
                'tipo' => $request->tipo,
                'monto' => $request->monto,
                'concepto' => $request->concepto,
                'referencia' => $request->referencia,
                'fecha' => $request->fecha,
            ]);

            if ($request->tipo === 'ingreso') {
                $banco->increment('saldo_actual', $request->monto);
            } else {
                $banco->decrement('saldo_actual', $request->monto);
            }

            DB::commit();
            return back()->with('success', 'Movimiento registrado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al registrar movimiento: ' . $e->getMessage());
        }
    }

    public function paseCajaBanco(Request $request)
    {
        $request->validate([
            'monto' => 'required|numeric|min:0.01',
            'concepto' => 'nullable|string',
            'cierre_caja_id' => 'required|integer',
        ]);

        $user = Auth::user();
        $banco = CuentaBancaria::preferidaParaUsuario($user);

        if (!$banco) {
            return response()->json(['success' => false, 'message' => 'No hay cuenta bancaria activa configurada.'], 422);
        }

        DB::beginTransaction();
        try {
            BancoMovimiento::create([
                'cuenta_bancaria_id' => $banco->id,
                'user_id' => $user->id,
                'tipo' => 'ingreso',
                'monto' => $request->monto,
                'concepto' => $request->concepto ?: 'Pase de caja a banco',
                'referencia' => 'Cierre Caja #' . $request->cierre_caja_id,
                'fecha' => now()->toDateString(),
                'cierre_caja_id' => $request->cierre_caja_id,
                'sucursal_id' => $user->branch_id,
            ]);

            $banco->increment('saldo_actual', $request->monto);

            DB::commit();
            return response()->json(['success' => true, 'banco_nombre' => $banco->banco_nombre]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(CuentaBancaria $banco)
    {
        $this->authorizeOwner($banco);

        if ($banco->movimientos()->count() > 0) {
            return redirect()->route('bancos.index')->with('error', 'No se puede eliminar una cuenta con movimientos registrados. Desactívela en su lugar.');
        }

        $banco->delete();
        return redirect()->route('bancos.index')->with('success', 'Cuenta bancaria eliminada correctamente.');
    }

    public function updateMovimiento(Request $request, CuentaBancaria $banco, $movimientoId)
    {
        $this->authorizeOwner($banco);

        $request->validate([
            'tipo' => 'required|in:ingreso,egreso',
            'monto' => 'required|numeric|min:0.01',
            'concepto' => 'required|string',
            'fecha' => 'required|date',
        ]);

        $movimiento = BancoMovimiento::where('cuenta_bancaria_id', $banco->id)->findOrFail($movimientoId);

        DB::beginTransaction();
        try {
            // Revertir el movimiento anterior del saldo
            if ($movimiento->tipo === 'ingreso') {
                $banco->decrement('saldo_actual', $movimiento->monto);
            } else {
                $banco->increment('saldo_actual', $movimiento->monto);
            }

            // Actualizar el movimiento
            $movimiento->update([
                'tipo' => $request->tipo,
                'monto' => $request->monto,
                'concepto' => $request->concepto,
                'referencia' => $request->referencia,
                'fecha' => $request->fecha,
            ]);

            // Aplicar el nuevo movimiento al saldo
            if ($request->tipo === 'ingreso') {
                $banco->increment('saldo_actual', $request->monto);
            } else {
                $banco->decrement('saldo_actual', $request->monto);
            }

            DB::commit();
            return back()->with('success', 'Movimiento actualizado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    public function destroyMovimiento(CuentaBancaria $banco, $movimientoId)
    {
        $this->authorizeOwner($banco);

        $movimiento = BancoMovimiento::where('cuenta_bancaria_id', $banco->id)->findOrFail($movimientoId);

        DB::beginTransaction();
        try {
            // Revertir el saldo
            if ($movimiento->tipo === 'ingreso') {
                $banco->decrement('saldo_actual', $movimiento->monto);
            } else {
                $banco->increment('saldo_actual', $movimiento->monto);
            }

            $movimiento->delete();

            DB::commit();
            return back()->with('success', 'Movimiento eliminado y saldo revertido.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }

    private function authorizeOwner(CuentaBancaria $banco)
    {
        if ($banco->company_id !== Auth::user()->company_id) {
            abort(403);
        }
    }
}
