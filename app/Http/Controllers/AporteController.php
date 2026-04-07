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
            'observaciones' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();
            
            // 1. Verificar que hay una caja abierta en sesión
            $cajaAbierta = getSelectedCaja();
            
            if (!$cajaAbierta) {
                return redirect()->back()
                    ->with('error', 'No hay una caja abierta. Debe seleccionar y abrir una caja antes de registrar un aporte.')
                    ->withInput();
            }

            // 2. Crear el registro contable del aporte
            $aporte = new Aporte($request->all());
            $aporte->user_id = Auth::id();
            $aporte->save();

            // 3. Crear la operación de caja física para que el dinero entre realmente
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
                'metodo_pago' => 'efectivo'
            ]);
            $operacionCaja->save();

            // 4. Actualizar el saldo de aportaciones de la caja en tiempo real
            $cajaAbierta->aportaciones = ($cajaAbierta->aportaciones ?? 0) + $aporte->monto;
            $cajaAbierta->save();

            DB::commit();

            $cajaNombre = $cajaAbierta->caja->nombre ?? 'Caja';
            return redirect()->route('aportes.index')
                ->with('success', "Aporte registrado correctamente. S/ " . number_format($aporte->monto, 2) . " agregado a {$cajaNombre}.");
                
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
