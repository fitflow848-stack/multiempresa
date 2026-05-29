<?php

namespace App\Http\Controllers;

use App\Models\ActivoCorriente;
use App\Models\ActivoCorrientePago;
use App\Models\TipoActivoCorriente;
use App\Models\Company;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
        if ($request->filled('nombre')) {
            $query->where('nombre', 'like', '%' . $request->nombre . '%');
        }
        if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecha_registro', '>=', $request->fecha_inicio);
        }
        if ($request->filled('fecha_fin')) {
            $query->whereDate('fecha_registro', '<=', $request->fecha_fin);
        }

        // Exportar a Excel
        if ($request->has('export')) {
            return $this->exportExcel($query->get());
        }

        $activos = $query->paginate(20);

        return view('activos_corrientes.index', compact('tipos', 'activos'));
    }

    private function exportExcel($activos)
    {
        $filename = 'activos_corrientes_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($activos) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['Fecha Registro', 'Tipo', 'Nombre', 'Método Pago', 'Documento', 'Monto', 'Estado', 'Fecha Cobro/Saldo']);

            foreach ($activos as $activo) {
                fputcsv($file, [
                    $activo->fecha_registro->format('d/m/Y'),
                    $activo->tipo->nombre ?? '-',
                    $activo->nombre,
                    $activo->metodo_pago ?? '-',
                    $activo->documento ?? '-',
                    number_format($activo->monto, 2),
                    $activo->is_settled ? 'SALDADO' : 'PENDIENTE',
                    $activo->updated_at && $activo->is_settled ? $activo->updated_at->format('d/m/Y') : '-',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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
     * Cobrar un activo corriente con soporte de pagos parciales, método de pago y recibo.
     */
    public function cobrar(Request $request, $id)
    {
        $request->validate([
            'monto_pago' => 'required|numeric|min:0.01',
            'metodo_pago' => 'required|string',
            'referencia'  => 'nullable|string|max:255',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $activo = ActivoCorriente::findOrFail($id);
        $user = auth()->user();

        if ($activo->is_settled) {
            return response()->json(['success' => false, 'message' => 'Este activo ya está completamente cobrado.'], 400);
        }

        $montoPago = floatval($request->monto_pago);
        $montoPendiente = $activo->monto_pendiente;

        if ($montoPago > $montoPendiente + 0.01) {
            return response()->json(['success' => false, 'message' => 'El monto ingresado supera el saldo pendiente (S/ ' . number_format($montoPendiente, 2) . ').'], 400);
        }
        $montoPago = min($montoPago, $montoPendiente);

        $metodoPago = $request->metodo_pago;
        $esEfectivo = strtolower($metodoPago) === 'efectivo';
        $esDigital = !$esEfectivo;

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $cajaAbierta = null;
            if ($esEfectivo) {
                try {
                    $cajaAbierta = requireSelectedCaja('cobrar el activo');
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\DB::rollBack();
                    return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
                }
            }

            $codigoComprobante = 'COB-' . strtoupper(Str::random(8));

            $pago = ActivoCorrientePago::create([
                'activo_corriente_id' => $activo->id,
                'user_id' => $user->id,
                'cierre_caja_id' => $cajaAbierta ? $cajaAbierta->id : null,
                'monto' => $montoPago,
                'fecha_pago' => now(),
                'metodo_pago' => $metodoPago,
                'referencia' => $request->referencia,
                'codigo_comprobante' => $codigoComprobante,
                'observaciones' => $request->observaciones,
            ]);

            $nuevoMontoCobrado = floatval($activo->monto_cobrado) + $montoPago;
            $nuevoPendiente = floatval($activo->monto) - $nuevoMontoCobrado;
            $isSettled = $nuevoPendiente <= 0.01;

            $activo->update([
                'monto_cobrado' => $nuevoMontoCobrado,
                'is_settled' => $isSettled,
            ]);

            if ($esEfectivo && $cajaAbierta) {
                $cajaAbierta->ingresos = floatval($cajaAbierta->ingresos ?? 0) + $montoPago;
                $cajaAbierta->save();

                \App\Models\OperacionCaja::create([
                    'company_id'     => $user->company_id,
                    'sucursal_id'    => $user->branch_id,
                    'cierre_caja_id' => $cajaAbierta->id,
                    'user_id'        => $user->id,
                    'tipo'           => 'ingreso',
                    'partida'        => 'Cobro Activo Corriente',
                    'concepto'       => 'Cobro: ' . $activo->nombre,
                    'importe'        => $montoPago,
                    'metodo_pago'    => $metodoPago,
                    'es_efectivo'    => 1,
                    'fecha'          => now(),
                ]);
            } elseif ($esDigital) {
                $banco = \App\Models\CuentaBancaria::preferidaParaUsuario();
                if ($banco) {
                    \App\Models\BancoMovimiento::create([
                        'cuenta_bancaria_id' => $banco->id,
                        'user_id'            => $user->id,
                        'tipo'               => 'ingreso',
                        'monto'              => $montoPago,
                        'concepto'           => 'Cobro Activo Corriente: ' . $activo->nombre . ' (' . $metodoPago . ')',
                        'referencia'         => $request->referencia,
                        'fecha'              => now()->toDateString(),
                        'sucursal_id'        => $user->branch_id,
                    ]);
                    $banco->increment('saldo_actual', $montoPago);
                }
            }

            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'success'    => true,
                'message'    => 'Cobro registrado correctamente.',
                'pago_id'    => $pago->id,
                'is_settled' => $isSettled,
                'monto_pendiente' => max(0, $nuevoPendiente),
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al cobrar: ' . $e->getMessage()], 500);
        }
    }

    public function historial($id)
    {
        $activo = ActivoCorriente::with(['pagos.user'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'activo'  => [
                'nombre'          => $activo->nombre,
                'monto'           => $activo->monto,
                'monto_cobrado'   => $activo->monto_cobrado ?? 0,
                'monto_pendiente' => $activo->monto_pendiente,
            ],
            'pagos' => $activo->pagos->map(fn($p) => [
                'id'                => $p->id,
                'monto'             => $p->monto,
                'fecha_pago'        => $p->fecha_pago->format('d/m/Y H:i'),
                'metodo_pago'       => $p->metodo_pago,
                'referencia'        => $p->referencia,
                'codigo_comprobante'=> $p->codigo_comprobante,
                'observaciones'     => $p->observaciones,
                'user'              => $p->user->name ?? 'Sistema',
            ]),
        ]);
    }

    public function comprobante($pago_id)
    {
        $pago = ActivoCorrientePago::with(['activoCorriente.tipo', 'user'])->findOrFail($pago_id);
        $activo = $pago->activoCorriente;
        $empresa = Company::find(auth()->user()->company_id);

        $logo = null;
        $logoPath = null;
        if ($empresa && $empresa->logo) {
            $path = $empresa->logo_path;
            if ($path && file_exists($path)) {
                $logoPath = $path;
            }
        }
        if ($logoPath) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoType = pathinfo($logoPath, PATHINFO_EXTENSION);
            $logo = 'data:image/' . $logoType . ';base64,' . $logoData;
        }

        $pdf = Pdf::loadView('activos_corrientes.comprobante_cobro', compact('pago', 'activo', 'empresa', 'logo'))
            ->setPaper([0, 0, 215, 600], 'portrait');

        return $pdf->stream('recibo_cobro_' . $pago->codigo_comprobante . '.pdf');
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
