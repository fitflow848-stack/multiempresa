<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Compra;
use App\Models\CompraLinea;
use Illuminate\Support\Facades\Log;

class ComprasController extends Controller
{
    public function index()
    {
        return view('compras.create'); // tu vista inicial
    }

    /**
     * Store purchase and lines, then redirect to success view.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'proveedor_id' => ['nullable','integer'],
            'fecha_emision' => ['nullable','date'],
            'fecha_pago' => ['nullable','date'],
            'moneda' => ['nullable','string'],
            'credito' => ['nullable'],
            'percepcion' => ['nullable'],
            'inc_impuesto' => ['nullable'],
            'tipo' => ['nullable','string'],
            'presupuesto' => ['nullable','string'],
            'local_destino' => ['nullable','string'],

            'total_bruto' => ['nullable','numeric'],
            'total_descuento' => ['nullable','numeric'],
            'bruto_neto' => ['nullable','numeric'],
            'total_impuesto' => ['nullable','numeric'],
            'total_neto' => ['nullable','numeric'],
            'flete' => ['nullable','numeric'],
            'total_pagar' => ['nullable','numeric'],

            // arrays for lines
            'product_id' => ['nullable','array'],
            'product_id.*' => ['nullable','integer'],
            'cb' => ['nullable','array'],
            'descripcion' => ['nullable','array'],
            'cantidad' => ['nullable','array'],
            'cantidad.*' => ['nullable','numeric'],
            'costo' => ['nullable','array'],
            'descuento' => ['nullable','array'],
            'vcpc' => ['nullable','array'],
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
            ]);

            // guardar lineas
            $productIds = $request->input('product_id', []);
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

                CompraLinea::create([
                    'compra_id' => $compra->id,
                    'product_id' => $productIds[$i] ?? null,
                    'cb' => $cbs[$i] ?? null,
                    'descripcion' => $descripcion,
                    'cantidad' => (int)$cantidad,
                    'costo' => isset($costos[$i]) && $costos[$i] !== '' ? $costos[$i] : null,
                    'descuento' => isset($descs[$i]) && $descs[$i] !== '' ? $descs[$i] : 0,
                    'vcpc' => $vcpcs[$i] ?? null,
                ]);
            }

            DB::commit();

            // redirect to success page
            return redirect()->route('compras.success', $compra->id);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error guardando compra: '.$e->getMessage());
            return redirect()->back()->withInput()->withErrors(['general' => 'Ocurrió un error guardando la compra.']);
        }
    }

    /**
     * Success page after creating a purchase
     */
    public function success(Compra $compra)
    {
        return view('compras.success', compact('compra'));
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
            'received_at' => ['nullable','date'],
        ]);

        $compra->received_at = $data['received_at'] ?? now();
        $compra->save();

        return redirect()->route('compras.show', $compra->id)->with('success', 'Compra recibida correctamente.');
    }
}