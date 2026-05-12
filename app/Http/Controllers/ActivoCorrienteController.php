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

        // Obtener el nombre del tipo para usarlo como partida en caja
        $tipoActivo = TipoActivoCorriente::find($data['tipo_activo_corriente_id']);
        $partidaCaja = $tipoActivo ? $tipoActivo->nombre : 'Activo Corriente';

        try {
            if ($esEfectivo) {
                // Egreso de caja (sustracción - salida de dinero)
                $cajaAbierta = getSelectedCaja();
                if (!$cajaAbierta) {
                    $cajaAbierta = \App\Models\CierreCaja::where('id_empresa', auth()->user()->company_id)
                        ->where('sucursal_id', auth()->user()->branch_id)
                        ->whereNull('fecha_cierre')
                        ->latest()->first();
                }
                if ($cajaAbierta) {
                    $cajaAbierta->sustracciones = floatval($cajaAbierta->sustracciones ?? 0) + $monto;
                    $cajaAbierta->save();

                    \App\Models\OperacionCaja::create([
                        'cierre_caja_id' => $cajaAbierta->id,
                        'user_id' => auth()->id(),
                        'tipo' => 'sustraccion',
                        'partida' => $partidaCaja,
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
                        'concepto' => $partidaCaja . ': ' . $activo->nombre,
                        'referencia' => $data['documento'] ?? null,
                        'fecha' => $data['fecha_registro'],
                        'sucursal_id' => auth()->user()->branch_id,
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

        // Si hay proveedor, filtrar por proveedor_id O mostrar los que no tienen proveedor asignado
        if ($proveedorId) {
            $proveedor = \App\Models\Proveedor::find($proveedorId);
            $nombreProv = $proveedor ? ($proveedor->nombre_comercial ?? $proveedor->nombre_legal) : '';
            
            $query->where(function ($q) use ($proveedorId, $nombreProv) {
                // Registros vinculados directamente al proveedor
                $q->where('proveedor_id', $proveedorId);
                
                // Registros sin proveedor_id que coincidan por nombre (registros antiguos)
                if ($nombreProv) {
                    $q->orWhere(function ($sub) use ($nombreProv) {
                        $sub->whereNull('proveedor_id')
                            ->where(function ($s) use ($nombreProv) {
                                $s->where('nombre', 'like', "%{$nombreProv}%")
                                  ->orWhere('observaciones', 'like', "%{$nombreProv}%");
                            });
                    });
                }
                
                // También mostrar los que no tienen proveedor asignado (para que el usuario pueda usarlos)
                $q->orWhereNull('proveedor_id');
            });
        }

        $anticipos = $query->orderBy('fecha_registro', 'desc')
            ->get(['id', 'nombre', 'monto', 'fecha_registro', 'documento', 'proveedor_id']);

        return response()->json($anticipos);
    }
}
