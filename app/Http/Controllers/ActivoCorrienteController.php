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

        // Obtener el tipo para verificar si afecta caja
        $tipoActivo = TipoActivoCorriente::find($data['tipo_activo_corriente_id']);
        $partidaCaja = $tipoActivo ? $tipoActivo->nombre : 'Activo Corriente';

        // Solo registrar egreso si el tipo afecta caja (Otros y tipos nuevos NO afectan)
        if ($tipoActivo && $tipoActivo->afecta_caja) {
            $monto = (float) $data['monto'];
            $metodoPago = $data['metodo_pago'] ?? 'Efectivo';
            $esEfectivo = strtolower($metodoPago) === 'efectivo';

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
                    // Egreso de banco (transferencia) - prioriza cuenta de la sucursal
                    $banco = \App\Models\CuentaBancaria::preferidaParaUsuario();
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
            'descripcion' => $request->descripcion,
            'afecta_caja' => false, // Nuevos tipos NO afectan caja por defecto
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
     * Cobrar un activo corriente (tipo Otros) - registra ingreso en caja.
     */
    public function cobrar(Request $request, $id)
    {
        $activo = ActivoCorriente::findOrFail($id);
        $user = auth()->user();

        try {
            $cajaAbierta = requireSelectedCaja('cobrar el activo');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $activo->is_settled = true;
            $activo->save();

            // Registrar en Caja como INGRESO (dinero entra a caja)
            $cajaAbierta->ingresos = ($cajaAbierta->ingresos ?? 0) + $activo->monto;
            $cajaAbierta->save();

            \App\Models\OperacionCaja::create([
                'company_id' => $user->company_id,
                'sucursal_id' => $user->branch_id,
                'cierre_caja_id' => $cajaAbierta->id,
                'user_id' => $user->id,
                'tipo' => 'ingreso',
                'partida' => 'Cobro Activo Corriente',
                'concepto' => 'Cobro: ' . $activo->nombre,
                'importe' => $activo->monto,
                'metodo_pago' => 'Efectivo',
                'es_efectivo' => 1,
            ]);

            \Illuminate\Support\Facades\DB::commit();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return redirect()->back()->with('error', 'Error al cobrar: ' . $e->getMessage());
        }

        return redirect()->route('activos_corrientes.index')
            ->with('success', 'Activo cobrado correctamente y registrado en caja.');
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
