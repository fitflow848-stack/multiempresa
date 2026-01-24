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
                'proveedores.nombre_comercial as proveedor',
                'compras.total_neto',
                'compras.received_at'
            );

        $recordsTotal = Compra::count();

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
            $acciones = '';
            $acciones .= '<a href="' . route('compras.show', $r->id) . '" class="btn btn-sm btn-primary me-1">Ver</a>';
            if (! $r->received_at) {
                $acciones .= '<a href="' . route('compras.receive', $r->id) . '" class="btn btn-sm btn-warning">Recibir</a>';
            }
            return [
                'id' => $r->id,
                'fecha' => $r->fecha_emision ? date('Y-m-d', strtotime($r->fecha_emision)) : null,
                'proveedor' => $r->proveedor,
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

            $n = max(
                count($productIds),
                count($cbs),
                count($descrs),
                count($cants),
                count($costos),
                count($descs),
                count($vcpcs)
            );

            for ($i = 0; $i < $n; $i++) {
                // skip empty rows (cantidad zero and empty description)
                $cantidad = isset($cants[$i]) ? (float)$cants[$i] : 0;
                $descripcion = $descrs[$i] ?? null;
                if ($cantidad <= 0 && !$descripcion) continue;
                $productLines = ProductoLinea::where('id', $productLinesIds[$i] ?? 0)->first();
                CompraLinea::create([
                    'compra_id' => $compra->id,
                    'product_id' => $productIds[$i] ?? null,
                    'product_linea_id' => $productLinesIds[$i] ?? null,
                    'cb' => $cbs[$i] ?? null,
                    'descripcion' => $descripcion,
                    'cantidad' => (int)$cantidad,
                    'costo' => isset($costos[$i]) && $costos[$i] !== '' ? $costos[$i] : null,
                    'descuento' => isset($descs[$i]) && $descs[$i] !== '' ? $descs[$i] : 0,
                    'vcpc' => $vcpcs[$i] ?? null,
                    'pvp' => $productLines->pvp,
                    'pvp_dto' => $productLines->pvp_dto,
                    'pvc' => $productLines->pvc,
                    'pvc_dto' => $productLines->pvc_dto,
                    'stock_min' => $productLines->stock_minimo,
                    'stock_max' => $productLines->stock_maximo,
                    'lote' => $productLines->lote,
                    'fecha_vencimiento' => $productLines->fecha_venc,
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
}
