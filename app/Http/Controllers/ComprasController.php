<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Compra;
use App\Models\CompraLinea;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Producto;
use App\Models\ProductoLinea;
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
                'compras.credito'
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

        $orderColIndex = intval($request->input('order.0.column', 1));
        $orderDir = $request->input('order.0.dir', 'desc');
        $orderColumn = $columns[$orderColIndex] ?? 'compras.fecha_emision';

        // If ordering by received_at (estado), order by that column
        $query->orderBy($orderColumn, $orderDir);

        $recordsFiltered = $query->count();

        $rows = $query->skip($start)->take($length)->get();

        $data = $rows->map(function ($r) {
            $estado = $r->received_at ? 'Recibida' : 'Pendiente';
            $condicion = $r->credito ? 'Crédito' : 'Contado';
            $acciones = '';
            $acciones .= '<a href="' . route('compras.show', $r->id) . '" class="btn btn-sm btn-primary me-1">Ver</a>';
            if (! $r->received_at) {
                $acciones .= '<a href="' . route('compras.receive', $r->id) . '" class="btn btn-sm btn-warning me-1">Recibir</a>';
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

                // SI el precio es nuevo (o simplemente lo enviamos), afecta a TODO el producto
                // Usuario: "si el precio es nuevo debería afectar a todos los lotes de ese producto"
                if ($productId) {
                    // 1. Actualizar Producto (Padre)
                    DB::table('productos')->where('id', $productId)->update([
                        'pvp' => $newPvp,
                        'pvc' => $newPvc,
                        'pvp_dto' => $newPvpDto,
                        'pvc_dto' => $newPvcDto,
                        'pv_docena' => $newPvDocena,
                    ]);

                    // 2. Actualizar todas las Líneas/Variantes del producto
                    ProductoLinea::where('producto_id', $productId)->update([
                        'pvp' => $newPvp,
                        'pvc' => $newPvc,
                        'pvp_dto' => $newPvpDto,
                        'pvc_dto' => $newPvcDto,
                        'pv_docena' => $newPvDocena,
                    ]);

                    // 3. Actualizar todos los lotes activos en el almacén (para POS y vista Almacén)
                    DB::table('almacen_ingreso_detalle')
                        ->where('producto_id', $productId)
                        ->update([
                            'pvp' => $newPvp,
                            'pvc' => $newPvc,
                            'pvpd' => $newPvpDto, // Pvpd = PvP con Descuento (usado en Almacén y POS)
                            'pvcd' => $newPvcDto, // Pvcd = PVC con Descuento
                            // Nota: Si pv_docena no existe en esta tabla, se consume del producto en otros puntos
                        ]);
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
        return view('compras.show', compact('compra'));
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
     * Store reception (mark as received)
     */
    public function storeReception(Request $request, Compra $compra)
    {
        $data = $request->validate([
            'received_at' => ['nullable', 'date'],
        ]);

        $compra->received_at = $data['received_at'] ?? now();
        $compra->save();

        // Redirect to processing step where products can be received into almacén
        return redirect()->route('compras.receive.process', $compra->id)->with('success', 'Compra marcada como recibida. Continúe con el ingreso de productos.');
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
        $compra->load('lineas');

        DB::beginTransaction();
        try {
            foreach ($compra->lineas as $line) {
                if ($line->product_id) {
                    $producto = Producto::find($line->product_id);
                    if ($producto) {
                        $producto->cantidad = ($producto->cantidad ?? 0) + (int)$line->cantidad;
                        $producto->save();
                    }
                }
            }

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

        if ($compra) {
            DB::beginTransaction();
            try {
                // mark as received if not already
                if (! $compra->received_at) {
                    $compra->received_at = now();
                    $compra->save();
                }

                foreach ($compra->lineas as $line) {
                    if ($line->product_id) {
                        $producto = Producto::find($line->product_id);
                        if ($producto) {
                            $producto->cantidad = ($producto->cantidad ?? 0) + (int)$line->cantidad;
                            $producto->save();
                        }
                    }
                }

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
