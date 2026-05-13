<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Compra;
use App\Models\CompraLinea;
use App\Models\AlmacenIngreso;
use App\Models\AlmacenIngresoDetalle;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\BancoMovimiento;
use App\Models\CuentaBancaria;
use App\Models\Producto;
use App\Models\ProductoLinea;
use App\Models\Proveedor;
use App\Models\Sucursal;
use Barryvdh\DomPDF\Facade\Pdf;

class ComprasController extends Controller
{
    public function index()
    {
        return view('compras.index');
    }

    /**
     * Data endpoint for DataTables server-side processing
     */
    public function data(Request $request)
    {
        $columns = [
            'compras.id',
            'compras.fecha_emision',
            'proveedores.nombre_comercial',
            'compras.total_pagar',
            'compras.received_at'
        ];

        $draw = intval($request->input('draw'));
        $start = intval($request->input('start', 0));
        $length = intval($request->input('length', 10));
        $search = $request->input('search.value');
        $proveedorFilter = $request->input('proveedor_filter');
        $fechaDesde = $request->input('fecha_desde');
        $fechaHasta = $request->input('fecha_hasta');
        $estadoFilter = $request->input('estado_filter');

        $query = Compra::leftJoin('proveedores', 'compras.proveedor_id', 'proveedores.id')
            ->select(
                'compras.id',
                'compras.fecha_emision',
                'compras.tipo',
                'compras.serie_comprobante',
                'compras.numero_comprobante',
                'proveedores.nombre_comercial as proveedor',
                'compras.total_neto',
                'compras.received_at',
                'compras.credito',
                DB::raw("(SELECT p.estado FROM pasivos p WHERE p.compra_id = compras.id LIMIT 1) as pasivo_estado")
            )
            ->where('compras.company_id', Auth::user()->company_id)
            ->where('compras.local_destino', Auth::user()->branch_id);

        $recordsTotal = Compra::where('company_id', Auth::user()->company_id)
            ->where('local_destino', Auth::user()->branch_id)
            ->count();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('compras.id', 'like', "%{$search}%")
                    ->orWhere('compras.fecha_emision', 'like', "%{$search}%")
                    ->orWhere('proveedores.nombre_comercial', 'like', "%{$search}%")
                    ->orWhere('compras.total_neto', 'like', "%{$search}%");
            });
        }

        if ($proveedorFilter) {
            $query->where('proveedores.nombre_comercial', 'like', "%{$proveedorFilter}%");
        }

        if ($fechaDesde) {
            $query->whereDate('compras.fecha_emision', '>=', $fechaDesde);
        }

        if ($fechaHasta) {
            $query->whereDate('compras.fecha_emision', '<=', $fechaHasta);
        }

        if ($estadoFilter === 'completado') {
            $query->whereNotNull('compras.received_at');
        } elseif ($estadoFilter === 'pendiente') {
            $query->whereNull('compras.received_at');
        }

        $orderColIndex = intval($request->input('order.0.column', 1));
        $orderDir = $request->input('order.0.dir', 'desc');
        $orderColumn = $columns[$orderColIndex] ?? 'compras.fecha_emision';

        // If ordering by received_at (estado), order by that column
        $query->orderBy($orderColumn, $orderDir);

        $recordsFiltered = $query->count();

        $rows = $query->skip($start)->take($length)->get();

        $data = $rows->map(function ($r) {
            $estado = $r->received_at ? 'Recibida' : 'Pendiente';
            
            // Determinar condición de pago
            if ($r->credito) {
                $condicion = ($r->pasivo_estado === 'pagado') ? 'Pagado' : 'Crédito';
            } else {
                $condicion = 'Contado';
            }
            
            $acciones = '';
            $acciones .= '<a href="' . route('compras.show', $r->id) . '" class="btn btn-sm btn-primary me-1">Ver</a>';
            if (! $r->received_at) {
                $acciones .= '<a href="' . route('recibir-productos.index', ['id' => $r->id]) . '" class="btn btn-sm btn-warning me-1">Recibir</a>';
            }
            if (Auth::user()->can('compras.eliminar')) {
                $acciones .= '<button class="btn btn-sm btn-danger btn-delete-compra" data-id="' . $r->id . '" title="Eliminar compra"><i class="bx bxs-trash"></i></button>';
            }
            return [
                'id' => $r->id,
                'fecha' => $r->fecha_emision ? date('Y-m-d', strtotime($r->fecha_emision)) : null,
                'documento' => $r->tipo . ' ' . $r->serie_comprobante . '-' . $r->numero_comprobante,
                'proveedor' => $r->proveedor,
                'condicion' => $condicion,
                'total' => (float) $r->total_neto,
                'estado' => $estado,
                'acciones' => $acciones,
            ];
        })->toArray();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function create()
    {
        return view('compras.create');
    }

    /**
     * Store purchase and lines, then redirect to success view.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'proveedor_id' => ['nullable', 'integer'],
            'fecha_emision' => ['nullable', 'date'],
            'fecha_pago' => ['nullable', 'date'],
            'moneda' => ['nullable', 'string'],
            'credito' => ['nullable'],
            'metodo_pago_contado' => ['nullable', 'in:caja,banco,anticipo,otros_sd'],
            'anticipo_id' => ['nullable', 'integer'],
            'percepcion' => ['nullable'],
            'inc_impuesto' => ['nullable'],
            'tipo' => ['nullable', 'string'],
            'serie_comprobante' => ['nullable', 'string'],
            'numero_comprobante' => ['nullable', 'string'],
            'presupuesto' => ['nullable', 'string'],
            'local_destino' => ['nullable', 'string'],

            'total_bruto' => ['nullable', 'numeric'],
            'total_descuento' => ['nullable', 'numeric'],
            'bruto_neto' => ['nullable', 'numeric'],
            'total_impuesto' => ['nullable', 'numeric'],
            'total_neto' => ['nullable', 'numeric'],
            'flete' => ['nullable', 'numeric'],
            'total_pagar' => ['nullable', 'numeric'],

            // arrays for lines
            'product_id' => ['nullable', 'array'],
            'product_id.*' => ['nullable', 'integer'],
            'cb' => ['nullable', 'array'],
            'descripcion' => ['nullable', 'array'],
            'cantidad' => ['nullable', 'array'],
            'cantidad.*' => ['nullable', 'numeric'],
            'costo' => ['nullable', 'array'],
            'descuento' => ['nullable', 'array'],
            'vcpc' => ['nullable', 'array'],
            'pvp' => ['nullable', 'array'],
            'pvc_dto' => ['nullable', 'array'],
            'pv_docena' => ['nullable', 'array'],
            'precio_modificado' => ['nullable', 'array'],
        ]);

        // Guardar en transacción
        DB::beginTransaction();
        try {
            $compra = Compra::create([
                'proveedor_id' => $data['proveedor_id'] ?? null,
                'fecha_emision' => $data['fecha_emision'] ?? null,
                'fecha_pago' => $data['fecha_pago'] ?? null,
                'moneda' => $data['moneda'] ?? 'sol',
                'credito' => $request->has('credito') ? 1 : 0,
                'percepcion' => $request->has('percepcion') ? 1 : 0,
                'inc_impuesto' => $request->has('inc_impuesto') ? 1 : 0,
                'tipo' => $data['tipo'] ?? null,
                'serie_comprobante' => $data['serie_comprobante'] ?? null,
                'numero_comprobante' => $data['numero_comprobante'] ?? null,
                'presupuesto' => $data['presupuesto'] ?? null,
                'local_destino' => $data['local_destino'] ?? null,
                'total_bruto' => $data['total_bruto'] ?? 0,
                'total_descuento' => $data['total_descuento'] ?? 0,
                'bruto_neto' => $data['bruto_neto'] ?? 0,
                'total_impuesto' => $data['total_impuesto'] ?? 0,
                'total_neto' => $data['total_neto'] ?? 0,
                'flete' => $data['flete'] ?? 0,
                'total_pagar' => $data['total_pagar'] ?? 0,
                'id_usuario' => Auth::id(),
            ]);

            // guardar lineas
            $productIds = $request->input('product_id', []);
            $productLinesIds = $request->input('linea_id', []);
            $cbs = $request->input('cb', []);
            $descrs = $request->input('descripcion', []);
            $cants = $request->input('cantidad', []);
            $costos = $request->input('costo', []);
            $descs = $request->input('descuento', []);
            $vcpcs = $request->input('vcpc', []);
            $lotes = $request->input('lote', []);
            $vencimientos = $request->input('fecha_vencimiento', []);
            $stockMins = $request->input('stock_min', []);
            $stockMaxs = $request->input('stock_max', []);
            $pvps = $request->input('pvp', []);
            $pvcs = $request->input('pvc', []);
            $pvpDtos = $request->input('pvp_dto', []);
            $pvcDtos = $request->input('pvc_dto', []);
            $pvDocenas = $request->input('pv_docena', []);
            $preciosModificados = $request->input('precio_modificado', []);

            $n = max(
                count($productIds),
                count($cbs),
                count($descrs),
                count($cants),
                count($costos),
                count($descs),
                count($vcpcs),
                count($lotes),
                count($vencimientos)
            );

            for ($i = 0; $i < $n; $i++) {
                $cantidad = isset($cants[$i]) ? (float)$cants[$i] : 0;
                $descripcion = $descrs[$i] ?? null;
                $productId = $productIds[$i] ?? null;

                if ($cantidad <= 0 && !$descripcion) continue;

                $productLineId = $productLinesIds[$i] ?? 0;
                $productLine = ProductoLinea::find($productLineId);

                // Nuevos precios desde el request
                $newPvp = isset($pvps[$i]) ? (float)$pvps[$i] : ($productLine->pvp ?? 0);
                $newPvc = isset($pvcs[$i]) ? (float)$pvcs[$i] : ($productLine->pvc ?? 0);
                $newPvpDto = isset($pvpDtos[$i]) ? (float)$pvpDtos[$i] : ($productLine->pvp_dto ?? 0);
                $newPvcDto = isset($pvcDtos[$i]) ? (float)$pvcDtos[$i] : ($productLine->pvc_dto ?? 0);
                $newPvDocena = isset($pvDocenas[$i]) ? (float)$pvDocenas[$i] : ($productLine->pv_docena ?? 0);

                // Solo actualizar precios en producto/lotes si el usuario modificó precios
                $precioFueModificado = !empty($preciosModificados[$i]);

                if ($precioFueModificado) {
                    // Actualizar solo los lotes del local destino (no afectar otros locales)
                    if ($productId && !empty($data['local_destino'])) {
                        DB::table('almacen_ingreso_detalle')
                            ->where('producto_id', $productId)
                            ->whereIn('ingreso_id', function ($q) use ($data, $compra) {
                                $q->select('id')
                                    ->from('almacen_ingresos')
                                    ->where('sucursal_id', $data['local_destino'])
                                    ->where('company_id', $compra->company_id);
                            })
                            ->update([
                                'pvp'  => $newPvp,
                                'pvc'  => $newPvc,
                                'pvpd' => $newPvpDto,
                                'pvcd' => $newPvcDto,
                            ]);
                    }

                    // Actualizar precios de venta en producto_lineas y producto principal
                    // NO se sincronizan globalmente - los precios por sucursal se leen de almacen_ingreso_detalle
                    // Solo se actualizan los lotes de la sucursal destino (ya hecho arriba)
                }

                // Actualizar precio de compra siempre (es dato de la compra actual)
                $newCosto = isset($costos[$i]) && $costos[$i] !== '' ? (float)$costos[$i] : null;
                if ($newCosto !== null && $productLine) {
                    $productLine->update(['precio_compra' => $newCosto]);
                }
                if ($newCosto !== null && $productId) {
                    Producto::where('id', $productId)->update(['precio_compra' => $newCosto]);
                }

                CompraLinea::create([
                    'compra_id' => $compra->id,
                    'product_id' => $productId,
                    'product_linea_id' => $productLineId,
                    'cb' => $cbs[$i] ?? null,
                    'descripcion' => $descripcion,
                    'cantidad' => (int)$cantidad,
                    'costo' => isset($costos[$i]) && $costos[$i] !== '' ? $costos[$i] : null,
                    'descuento' => isset($descs[$i]) && $descs[$i] !== '' ? $descs[$i] : 0,
                    'vcpc' => $vcpcs[$i] ?? null,
                    'pvp' => $newPvp,
                    'pvp_dto' => $newPvpDto,
                    'pvc' => $newPvc,
                    'pvc_dto' => $newPvcDto,
                    'stock_min' => $stockMins[$i] ?? ($productLine->stock_minimo ?? 0),
                    'stock_max' => $stockMaxs[$i] ?? ($productLine->stock_maximo ?? 0),
                    'lote' => $lotes[$i] ?? ($productLine->lote ?? null),
                    'fecha_vencimiento' => $vencimientos[$i] ?? ($productLine->fecha_venc ?? null),
                ]);
            }

            DB::commit();

            // Si es compra a crédito, registrar automáticamente en pasivos (cuentas por pagar)
            if ($compra->credito) {
                try {
                    $tipoPasivo = \App\Models\TipoPasivo::firstOrCreate(
                        ['nombre' => 'Compras a crédito', 'company_id' => $compra->company_id],
                        ['descripcion' => 'Compras a crédito registradas automáticamente']
                    );

                    $proveedor = Proveedor::find($compra->proveedor_id);
                    \App\Models\Pasivo::create([
                        'company_id' => $compra->company_id,
                        'sucursal_id' => $compra->local_destino ?? Auth::user()->branch_id,
                        'compra_id' => $compra->id,
                        'tipo_pasivo_id' => $tipoPasivo->id,
                        'nombre' => $proveedor->nombre_comercial ?? 'Proveedor',
                        'empresa_persona' => $proveedor->nombre_comercial ?? null,
                        'monto' => $compra->total_pagar,
                        'monto_pagado' => 0,
                        'estado' => 'pendiente',
                        'fecha_registro' => $compra->fecha_emision ?? now(),
                        'documento' => trim(($compra->serie_comprobante ?? '') . ' ' . ($compra->numero_comprobante ?? '')),
                        'observaciones' => 'Compra a crédito #' . $compra->id . ' registrada automáticamente.',
                        'user_id' => Auth::id(),
                        'is_compra_credito' => true,
                    ]);
                } catch (\Throwable $pe) {
                    Log::error('Error registrando pasivo de compra a crédito: ' . $pe->getMessage());
                }
            } else {
                // Compra al CONTADO: registrar movimiento según método de pago elegido
                $metodoPago = $data['metodo_pago_contado'] ?? 'caja';
                try {
                    if ($metodoPago === 'banco') {
                        $banco = \App\Models\CuentaBancaria::preferidaParaUsuario();
                        if ($banco) {
                            \App\Models\BancoMovimiento::create([
                                'cuenta_bancaria_id' => $banco->id,
                                'user_id' => Auth::id(),
                                'tipo' => 'egreso',
                                'monto' => $compra->total_pagar,
                                'concepto' => 'Pago compra #' . $compra->id . ' a proveedor',
                                'referencia' => trim(($compra->serie_comprobante ?? '') . ' ' . ($compra->numero_comprobante ?? '')),
                                'fecha' => now()->toDateString(),
                                'sucursal_id' => Auth::user()->branch_id,
                            ]);
                            $banco->decrement('saldo_actual', $compra->total_pagar);
                        }
                    } elseif ($metodoPago === 'caja') {
                        // Registrar egreso en la caja activa
                        $cajaAbierta = getSelectedCaja();
                        if (!$cajaAbierta) {
                            $cajaAbierta = \App\Models\CierreCaja::where('id_empresa', $compra->company_id)
                                ->where('sucursal_id', Auth::user()->branch_id)
                                ->whereNull('fecha_cierre')
                                ->latest()->first();
                        }
                        if ($cajaAbierta) {
                            $cajaAbierta->egresos = floatval($cajaAbierta->egresos ?? 0) + $compra->total_pagar;
                            $cajaAbierta->save();

                            \App\Models\OperacionCaja::create([
                                'cierre_caja_id' => $cajaAbierta->id,
                                'user_id' => Auth::id(),
                                'tipo' => 'egreso',
                                'partida' => 'Compra',
                                'concepto' => 'Pago compra #' . $compra->id . ' - ' . trim(($compra->serie_comprobante ?? '') . ' ' . ($compra->numero_comprobante ?? '')),
                                'importe' => $compra->total_pagar,
                                'metodo_pago' => 'Efectivo',
                                'es_efectivo' => 1,
                            ]);
                        }
                    } elseif ($metodoPago === 'anticipo') {
                        // Saldar el anticipo a proveedor seleccionado
                        $anticipoId = $data['anticipo_id'] ?? null;
                        if (!$anticipoId) {
                            throw new \Exception('Debe seleccionar un anticipo para usar este método de pago.');
                        }
                        if ($anticipoId) {
                            $anticipo = \App\Models\ActivoCorriente::find($anticipoId);
                            if ($anticipo) {
                                $montoAnticipo = (float) $anticipo->monto;
                                $totalCompra = (float) $compra->total_pagar;

                                // Marcar anticipo como saldado
                                $anticipo->update([
                                    'is_settled' => true,
                                    'monto' => 0,
                                    'observaciones' => ($anticipo->observaciones ? $anticipo->observaciones . ' | ' : '') . 'Saldado con compra #' . $compra->id . ' (Monto original: S/' . number_format($montoAnticipo, 2) . ')'
                                ]);

                                // Si la compra cuesta más que el anticipo, la diferencia sale de caja
                                $diferencia = $totalCompra - $montoAnticipo;
                                if ($diferencia > 0.01) {
                                    $cajaAbierta = getSelectedCaja();
                                    if (!$cajaAbierta) {
                                        $cajaAbierta = \App\Models\CierreCaja::where('id_empresa', $compra->company_id)
                                            ->where('sucursal_id', Auth::user()->branch_id)
                                            ->whereNull('fecha_cierre')
                                            ->latest()->first();
                                    }
                                    if ($cajaAbierta) {
                                        $cajaAbierta->egresos = floatval($cajaAbierta->egresos ?? 0) + $diferencia;
                                        $cajaAbierta->save();

                                        \App\Models\OperacionCaja::create([
                                            'cierre_caja_id' => $cajaAbierta->id,
                                            'user_id' => Auth::id(),
                                            'tipo' => 'egreso',
                                            'partida' => 'Diferencia Compra (Anticipo)',
                                            'concepto' => 'Diferencia compra #' . $compra->id . ' (Total: S/' . number_format($totalCompra, 2) . ' - Anticipo: S/' . number_format($montoAnticipo, 2) . ')',
                                            'importe' => $diferencia,
                                            'metodo_pago' => 'Efectivo',
                                            'es_efectivo' => 1,
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                } catch (\Throwable $pe) {
                    Log::error('Error registrando pago contado de compra: ' . $pe->getMessage());
                }
            }

            // redirect to success page
            return redirect()->route('compras.success', $compra->id);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error guardando compra: ' . $e->getMessage());
            return redirect()->back()->withInput()->withErrors(['general' => 'Ocurrió un error guardando la compra.']);
        }
    }

    /**
     * Success page after creating a purchase
     */
    public function success(Compra $compra)
    {
        $almacenes = Sucursal::where('company_id', Auth::user()->company_id)->get();

        return view('compras.success', compact('compra', 'almacenes'));
    }

    /**
     * Update the selected local_destino for a compra (AJAX)
     */
    public function updateLocalDestino(Request $request, Compra $compra)
    {
        $data = $request->validate([
            'local_destino' => ['nullable', 'string']
        ]);

        $compra->local_destino = $data['local_destino'] ?? null;
        $compra->save();

        return response()->json(['success' => true]);
    }

    /**
     * Show purchase details
     */
    public function show(Compra $compra)
    {
        $compra->load('lineas');

        // Cargar detalles de almacén para generar etiquetas (solo si la compra fue recibida)
        $almacenDetalles = collect();
        if ($compra->received_at) {
            $almacenDetalles = \App\Models\AlmacenIngresoDetalle::with(['producto', 'productoLinea'])
                ->whereHas('ingreso', fn($q) => $q->where('compra_id', $compra->id))
                ->get();
        }

        return view('compras.show', compact('compra', 'almacenDetalles'));
    }

    /**
     * PDF view for compra
     */
    public function pdf(Compra $compra)
    {
        $compra->load(['lineas', 'proveedor', 'usuario']);
        $company = Auth::user()->company;
        
        $logoPath = null;
        if ($company && $company->logo) {
            $logoFilePath = $company->logo_path;
            if ($logoFilePath && file_exists($logoFilePath)) {
                $logoPath = 'data:image/png;base64,' . base64_encode(file_get_contents($logoFilePath));
            }
        }
        
        if (!$logoPath) {
            $defaultLogoPath = public_path('images/scorpion.png');
            if (file_exists($defaultLogoPath)) {
                $logoPath = 'data:image/png;base64,' . base64_encode(file_get_contents($defaultLogoPath));
            }
        }

        $pdf = Pdf::loadView('compras.pdf', compact('compra', 'company', 'logoPath'));
        return $pdf->stream('compra_' . $compra->id . '.pdf');
    }

    /**
     * Show reception form
     */
    public function receiveForm(Compra $compra)
    {
        $compra->load('lineas');
        return view('compras.receive', compact('compra'));
    }

    /**
     * Store reception — recibe la compra completa en un solo paso:
     * crea AlmacenIngreso + detalles, actualiza stock, marca recibido=1.
     */
    public function storeReception(Request $request, Compra $compra)
    {
        if ($compra->recibido) {
            return redirect()->route('compras.show', $compra->id)->with('error', 'Esta compra ya fue recibida anteriormente. No se puede procesar dos veces.');
        }

        $request->validate([
            'observaciones' => ['nullable', 'string', 'max:500'],
        ]);

        $compra->load('lineas');
        $sucursalId = $compra->local_destino ?? Auth::user()->branch_id;

        DB::beginTransaction();
        try {
            // Crear ingreso en almacén
            $ingreso = AlmacenIngreso::create([
                'company_id' => $compra->company_id,
                'empresa_id' => $compra->company_id,
                'sucursal_id' => $sucursalId,
                'user_id' => Auth::id(),
                'compra_id' => $compra->id,
                'fecha' => now(),
                'observacion' => $request->input('observaciones'),
            ]);

            foreach ($compra->lineas as $line) {
                if ($line->product_id) {
                    $producto = Producto::find($line->product_id);
                    if ($producto) {
                        $producto->cantidad = ($producto->cantidad ?? 0) + (int)$line->cantidad;
                        $producto->save();
                    }

                    $costoRecibido = $line->costo ?? 0;

                    AlmacenIngresoDetalle::create([
                        'ingreso_id' => $ingreso->id,
                        'producto_id' => $line->product_id,
                        'producto_linea_id' => $line->product_linea_id ?? null,
                        'cantidad' => $line->cantidad,
                        'costo' => $costoRecibido,
                        'cop' => $costoRecibido,
                        'mu' => 0,
                        'mud' => 0,
                        'mup' => 0,
                        'pvp' => $line->pvp ?? 0,
                        'pvpd' => $line->pvp_dto ?? 0,
                        'pvc' => $line->pvc ?? 0,
                        'pvcd' => $line->pvc_dto ?? 0,
                        'stock_min' => $line->stock_min ?? 0,
                        'stock_max' => $line->stock_max ?? 0,
                        'lote' => $line->lote ?? null,
                        'fecha_vencimiento' => $line->fecha_vencimiento ?? null,
                    ]);

                    // Sincronizar precio_compra con el costo real recibido
                    if ($line->product_linea_id && $costoRecibido > 0) {
                        ProductoLinea::where('id', $line->product_linea_id)
                            ->update(['precio_compra' => $costoRecibido]);
                    }
                    if ($costoRecibido > 0) {
                        Producto::where('id', $line->product_id)
                            ->update(['precio_compra' => $costoRecibido]);
                    }
                }
            }

            // Marcar compra como recibida
            $compra->update(['recibido' => 1, 'received_at' => now()]);

            DB::commit();

            return redirect()->route('compras.show', $compra->id)->with('success', 'Compra recibida y productos ingresados al almacén correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al recibir compra directa: ' . $e->getMessage());
            return redirect()->route('compras.receive', $compra->id)->with('error', 'Ocurrió un error: ' . $e->getMessage());
        }
    }

    /**
     * Show processing page to receive products into almacén
     */
    public function processReception(Compra $compra)
    {
        $compra->load('lineas');
        return view('compras.process', compact('compra'));
    }

    /**
     * Store reception of products into almacén (increase product stock)
     */
    public function storeReceptionProducts(Request $request, Compra $compra)
    {
        if ($compra->recibido) {
            return redirect()->route('compras.show', $compra->id)->with('error', 'Esta compra ya fue recibida anteriormente. No se puede procesar dos veces.');
        }

        $compra->load('lineas');
        $sucursalId = $compra->local_destino ?? Auth::user()->branch_id;

        DB::beginTransaction();
        try {
            // Crear ingreso en almacén
            $ingreso = AlmacenIngreso::create([
                'company_id' => $compra->company_id,
                'empresa_id' => $compra->company_id,
                'sucursal_id' => $sucursalId,
                'user_id' => Auth::id(),
                'compra_id' => $compra->id,
                'fecha' => now(),
                'observacion' => null,
            ]);

            foreach ($compra->lineas as $line) {
                if ($line->product_id) {
                    $producto = Producto::find($line->product_id);
                    if ($producto) {
                        $producto->cantidad = ($producto->cantidad ?? 0) + (int)$line->cantidad;
                        $producto->save();
                    }

                    $costoRecibido = $line->costo ?? 0;

                    AlmacenIngresoDetalle::create([
                        'ingreso_id' => $ingreso->id,
                        'producto_id' => $line->product_id,
                        'producto_linea_id' => $line->product_linea_id ?? null,
                        'cantidad' => $line->cantidad,
                        'costo' => $costoRecibido,
                        'cop' => $costoRecibido,
                        'mu' => 0,
                        'mud' => 0,
                        'mup' => 0,
                        'pvp' => $line->pvp ?? 0,
                        'pvpd' => $line->pvp_dto ?? 0,
                        'pvc' => $line->pvc ?? 0,
                        'pvcd' => $line->pvc_dto ?? 0,
                        'stock_min' => $line->stock_min ?? 0,
                        'stock_max' => $line->stock_max ?? 0,
                        'lote' => $line->lote ?? null,
                        'fecha_vencimiento' => $line->fecha_vencimiento ?? null,
                    ]);

                    // Sincronizar precio_compra con el costo real recibido
                    if ($line->product_linea_id && $costoRecibido > 0) {
                        ProductoLinea::where('id', $line->product_linea_id)
                            ->update(['precio_compra' => $costoRecibido]);
                    }
                    if ($costoRecibido > 0) {
                        Producto::where('id', $line->product_id)
                            ->update(['precio_compra' => $costoRecibido]);
                    }
                }
            }

            // Marcar compra como recibida
            $compra->update(['recibido' => 1, 'received_at' => now()]);

            DB::commit();

            return redirect()->route('compras.show', $compra->id)->with('success', 'Productos recibidos en almacén correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al recibir productos en almacén: ' . $e->getMessage());
            return redirect()->route('compras.receive.process', $compra->id)->with('error', 'Ocurrió un error procesando la recepción.');
        }
    }

    /**
     * Start a batch reception: store selected compra ids in session and start at index 0
     */
    public function startBatchReception(Request $request)
    {
        $ids = $request->input('ids', []);
        if (!is_array($ids) || empty($ids)) {
            return response()->json(['error' => 'No hay compras seleccionadas'], 422);
        }

        session(['compras_receive_ids' => array_values($ids), 'compras_receive_index' => 0]);

        return response()->json(['ok' => true]);
    }

    /**
     * Show current compra in batch processing
     */
    public function processBatch()
    {
        $ids = session('compras_receive_ids', []);
        $index = session('compras_receive_index', 0);

        if (empty($ids) || !isset($ids[$index])) {
            session()->forget(['compras_receive_ids', 'compras_receive_index']);
            return redirect()->route('compras.index')->with('success', 'Recepción por lotes completada.');
        }

        $compra = Compra::with('lineas')->find($ids[$index]);
        if (! $compra) {
            // skip invalid and advance
            session(['compras_receive_index' => $index + 1]);
            return redirect()->route('compras.receive.batch');
        }

        return view('compras.process_batch', compact('compra', 'index'));
    }

    /**
     * Receive current compra in batch and advance to next
     */
    public function receiveAndNext(Request $request)
    {
        $ids = session('compras_receive_ids', []);
        $index = session('compras_receive_index', 0);

        if (empty($ids) || !isset($ids[$index])) {
            session()->forget(['compras_receive_ids', 'compras_receive_index']);
            return redirect()->route('compras.index')->with('success', 'No hay compras para procesar.');
        }

        $compraId = $ids[$index];
        $compra = Compra::with('lineas')->find($compraId);

        if ($compra && !$compra->recibido) {
            DB::beginTransaction();
            try {
                $sucursalId = $compra->local_destino ?? Auth::user()->branch_id;

                // Crear ingreso en almacén
                $ingreso = AlmacenIngreso::create([
                    'company_id' => $compra->company_id,
                    'empresa_id' => $compra->company_id,
                    'sucursal_id' => $sucursalId,
                    'user_id' => Auth::id(),
                    'compra_id' => $compra->id,
                    'fecha' => now(),
                    'observacion' => null,
                ]);

                foreach ($compra->lineas as $line) {
                    if ($line->product_id) {
                        $producto = Producto::find($line->product_id);
                        if ($producto) {
                            $producto->cantidad = ($producto->cantidad ?? 0) + (int)$line->cantidad;
                            $producto->save();
                        }

                        $costoRecibido = $line->costo ?? 0;

                        AlmacenIngresoDetalle::create([
                            'ingreso_id' => $ingreso->id,
                            'producto_id' => $line->product_id,
                            'producto_linea_id' => $line->product_linea_id ?? null,
                            'cantidad' => $line->cantidad,
                            'costo' => $costoRecibido,
                            'cop' => $costoRecibido,
                            'mu' => 0,
                            'mud' => 0,
                            'mup' => 0,
                            'pvp' => $line->pvp ?? 0,
                            'pvpd' => $line->pvp_dto ?? 0,
                            'pvc' => $line->pvc ?? 0,
                            'pvcd' => $line->pvc_dto ?? 0,
                            'stock_min' => $line->stock_min ?? 0,
                            'stock_max' => $line->stock_max ?? 0,
                            'lote' => $line->lote ?? null,
                            'fecha_vencimiento' => $line->fecha_vencimiento ?? null,
                        ]);

                        // Sincronizar precio_compra con el costo real recibido
                        if ($line->product_linea_id && $costoRecibido > 0) {
                            ProductoLinea::where('id', $line->product_linea_id)
                                ->update(['precio_compra' => $costoRecibido]);
                        }
                        if ($costoRecibido > 0) {
                            Producto::where('id', $line->product_id)
                                ->update(['precio_compra' => $costoRecibido]);
                        }
                    }
                }

                // Marcar como recibida
                $compra->update(['recibido' => 1, 'received_at' => now()]);

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('Error en recepción por lotes: ' . $e->getMessage());
                return redirect()->route('compras.receive.batch')->with('error', 'Error procesando compra ' . $compraId);
            }
        }

        // advance index
        $index++;
        if ($index >= count($ids)) {
            session()->forget(['compras_receive_ids', 'compras_receive_index']);
            return redirect()->route('compras.index')->with('success', 'Recepción por lotes completada.');
        }

        session(['compras_receive_index' => $index]);
        return redirect()->route('compras.receive.batch');
    }

    /**
     * Delete purchase and revert stock if already received
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $compra = Compra::with(['lineas', 'ingresos.detalles'])->where('company_id', Auth::user()->company_id)->findOrFail($id);

            // 1. Si ya fue recibida, revertimos stock
            if ($compra->recibido) {
                foreach ($compra->lineas as $line) {
                    if ($line->product_id) {
                        $producto = Producto::find($line->product_id);
                        if ($producto) {
                            $producto->cantidad = (float)max(0, (float)($producto->cantidad ?? 0) - (float)$line->cantidad);
                            $producto->save();
                        }
                    }
                }

                // 2. Eliminar registros de AlmacenIngreso vinculados
                foreach ($compra->ingresos as $ingreso) {
                    $ingreso->detalles()->delete();
                    $ingreso->delete();
                }
            }

            // 3. Eliminar la compra (líneas se borran por cascada)
            $compra->delete();

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Compra eliminada y stock revertido correctamente.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error eliminando compra: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }
}
