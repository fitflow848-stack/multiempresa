<?php

namespace App\Http\Controllers;

use App\Models\ActivoCorriente;
use App\Models\TipoActivoCorriente;
use Illuminate\Http\Request;

class ActivoCorrienteController extends Controller
{
    public function index(Request $request)
    {
        // Asegurar que la empresa tenga los tipos por defecto
        \App\Helpers\AccountingHelper::ensureDefaults(auth()->user()->company_id);

        $tipos = TipoActivoCorriente::orderBy('nombre')
            ->get();

        $query = ActivoCorriente::with('tipo')
            ->orderBy('fecha_registro', 'desc');

        if ($request->filled('tipo_id')) {
            $query->where('tipo_activo_corriente_id', $request->tipo_id);
        }
        if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecha_registro', '>=', $request->fecha_inicio);
        }
        if ($request->filled('fecha_fin')) {
            $query->whereDate('fecha_registro', '<=', $request->fecha_fin);
        }

        $activos = $query->paginate(20);

        return view('activos_corrientes.index', compact('tipos', 'activos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo_activo_corriente_id' => 'required|exists:tipo_activo_corrientes,id',
            'nombre' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0',
            'fecha_registro' => 'required|date',
            'documento' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string',
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'metodo_pago' => 'required|string'
        ]);

        $data = $request->all();
        $data['user_id'] = auth()->id();

        // Si se seleccionó proveedor, auto-completar el nombre si está vacío
        if (!empty($data['proveedor_id']) && empty($data['nombre'])) {
            $proveedor = \App\Models\Proveedor::find($data['proveedor_id']);
            if ($proveedor) {
                $data['nombre'] = 'Anticipo - ' . ($proveedor->nombre_comercial ?? $proveedor->nombre_legal);
            }
        }

        $activo = ActivoCorriente::create($data);

        // Registrar egreso según método de pago
        $monto = (float) $data['monto'];
        $metodoPago = $data['metodo_pago'] ?? 'Efectivo';
        $esEfectivo = strtolower($metodoPago) === 'efectivo';

        try {
            if ($esEfectivo) {
                // Egreso de caja
                $cajaAbierta = getSelectedCaja();
                if (!$cajaAbierta) {
                    $cajaAbierta = \App\Models\CierreCaja::where('company_id', auth()->user()->company_id)
                        ->where('sucursal_id', auth()->user()->branch_id)
                        ->whereNull('fecha_cierre')
                        ->latest()->first();
                }
                if ($cajaAbierta) {
                    $cajaAbierta->egresos = floatval($cajaAbierta->egresos ?? 0) + $monto;
                    $cajaAbierta->save();

                    \App\Models\OperacionCaja::create([
                        'cierre_caja_id' => $cajaAbierta->id,
                        'user_id' => auth()->id(),
                        'tipo' => 'egreso',
                        'partida' => 'Anticipo a Proveedor',
                        'concepto' => $activo->nombre,
                        'importe' => $monto,
                        'metodo_pago' => $metodoPago,
                        'es_efectivo' => 1,
                    ]);

                    $activo->update(['cierre_caja_id' => $cajaAbierta->id]);
                }
            } else {
                // Egreso de banco (transferencia)
                $banco = \App\Models\CuentaBancaria::where('company_id', auth()->user()->company_id)
                    ->where('is_active', true)->orderBy('id')->first();
                if ($banco) {
                    \App\Models\BancoMovimiento::create([
                        'cuenta_bancaria_id' => $banco->id,
                        'user_id' => auth()->id(),
                        'tipo' => 'egreso',
                        'monto' => $monto,
                        'concepto' => 'Anticipo a proveedor: ' . $activo->nombre,
                        'referencia' => $data['documento'] ?? null,
                        'fecha' => $data['fecha_registro'],
                    ]);
                    $banco->decrement('saldo_actual', $monto);
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error registrando pago de activo corriente: ' . $e->getMessage());
        }

        return redirect()->route('activos_corrientes.index')->with('success', 'Activo corriente registrado correctamente');
    }

    public function storeTipo(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|unique:tipo_activo_corrientes,nombre|max:255',
            'descripcion' => 'nullable|string'
        ]);

        $tipo = TipoActivoCorriente::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tipo de activo corriente creado exitosamente',
                'tipo' => $tipo
            ]);
        }

        return redirect()->route('activos_corrientes.index')->with('success', 'Tipo creado');
    }

    public function destroy($id)
    {
        $activo = ActivoCorriente::findOrFail($id);
        $activo->delete();

        return redirect()->route('activos_corrientes.index')->with('success', 'Eliminado correctamente');
    }

    public function edit($id)
    {
        $activo = ActivoCorriente::findOrFail($id);
        return response()->json($activo);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tipo_activo_corriente_id' => 'required|exists:tipo_activo_corrientes,id',
            'nombre' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0',
            'fecha_registro' => 'required|date',
            'documento' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string'
        ]);

        $activo = ActivoCorriente::findOrFail($id);
        $activo->update($request->all());

        return redirect()->route('activos_corrientes.index')->with('success', 'Activo actualizado correctamente');
    }

    /**
     * Obtener anticipos pendientes (no saldados) de un proveedor específico.
     */
    public function anticiposProveedor(Request $request)
    {
        $proveedorId = $request->get('proveedor_id');
        $companyId = auth()->user()->company_id;

        $tipoAnticipo = TipoActivoCorriente::where('company_id', $companyId)
            ->where(function ($q) {
                $q->where('nombre', 'like', '%Anticipo%Proveedor%')
                  ->orWhere('nombre', 'like', '%Anticipo%proveedor%');
            })->first();

        if (!$tipoAnticipo) {
            return response()->json([]);
        }

        $query = ActivoCorriente::where('company_id', $companyId)
            ->where('tipo_activo_corriente_id', $tipoAnticipo->id)
            ->where('is_settled', false);

        // Filtrar por proveedor_id directamente si existe
        if ($proveedorId) {
            $query->where(function ($q) use ($proveedorId) {
                $q->where('proveedor_id', $proveedorId);
                
                // Fallback: buscar también por nombre para registros antiguos sin proveedor_id
                $proveedor = \App\Models\Proveedor::find($proveedorId);
                if ($proveedor) {
                    $nombreProv = $proveedor->nombre_comercial ?? $proveedor->nombre_legal;
                    $q->orWhere(function ($sub) use ($nombreProv) {
                        $sub->whereNull('proveedor_id')
                            ->where(function ($s) use ($nombreProv) {
                                $s->where('nombre', 'like', "%{$nombreProv}%")
                                  ->orWhere('observaciones', 'like', "%{$nombreProv}%");
                            });
                    });
                }
            });
        }

        $anticipos = $query->orderBy('fecha_registro', 'desc')->get(['id', 'nombre', 'monto', 'fecha_registro', 'documento']);

        return response()->json($anticipos);
    }
}
