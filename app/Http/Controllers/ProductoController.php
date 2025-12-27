<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use App\Models\UnidadMedida;
use App\Models\Producto;
use App\Models\ProductoLinea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ProductoController extends Controller
{
    public function step1()
    {
        $marcas = Marca::all();
        $unidades = UnidadMedida::all();
        return view('productos.create', compact('marcas', 'unidades'));
    }

    public function step2(Request $request)
    {
        // Validación mínima de los campos que vienen del quick form
        $data = $request->validate([
            'laboratorio' => ['nullable', 'string', 'max:255'],
            'familia_id' => ['nullable', 'integer'],
            'subfamilia_id' => ['nullable', 'integer'],
            'nombre' => ['required', 'string', 'max:1000'],
            'marca_id' => ['nullable', 'integer'],
            'unidad_medida_id' => ['nullable', 'integer'],
            'tipo_impuesto' => ['nullable', 'string'],
            'opciones_avanzadas' => ['nullable', 'boolean'],
            'condicion_venta' => ['nullable', 'string'],
            'attr_numero_serie' => ['nullable', 'boolean'],
            'attr_fecha_vencimiento' => ['nullable', 'boolean'],
            'attr_lote_produccion' => ['nullable', 'boolean'],
            'attr_venta_menudeo' => ['nullable', 'boolean'],
        ]);

        // Normalizar booleans que vengan como "on"
        foreach (['opciones_avanzadas', 'attr_numero_serie', 'attr_fecha_vencimiento', 'attr_lote_produccion', 'attr_venta_menudeo'] as $b) {
            if ($request->has($b)) {
                $data[$b] = in_array($request->input($b), ['1', 'true', 'on', 1, true], true) ? 1 : 0;
            } else {
                $data[$b] = 0;
            }
        }

        // Pasar datos a la vista detallada
        return view('productos.create-detailed', ['quick' => $data]);
    }

    /**
     * Guardar producto y sus líneas
     */
    public function store(Request $request)
    {
        // parse lines JSON
        $linesJson = $request->input('product_lines', '[]');
        $lines = json_decode($linesJson, true);
        if (!is_array($lines) || count($lines) === 0) {
            return redirect()->back()->withInput()->withErrors(['product_lines' => 'Debe agregar al menos una línea de producto.']);
        }

        DB::beginTransaction();
        try {
            $productoData = [
                'id_empresa' => Auth::user()->company_id,
                'nombre' => $request['nombre'],
                'laboratorio' => $request['laboratorio'] ?? null,
                'familia_id' => $request['familia_id'] ?? null,
                'subfamilia_id' => $request['subfamilia_id'] ?? null,
                'marca_id' => $request['marca_id'] ?? null,
                'unidad_medida_id' => $request['unidad_medida_id'] ?? null,
                'tipo_impuesto' => $request['tipo_impuesto'] ?? null,
                'opciones_avanzadas' => $request['opciones_avanzadas'] ?? false,
                'condicion_venta' => $request['condicion_venta'] ?? null,
                'attr_numero_serie' => $request['attr_numero_serie'] ?? false,
                'attr_fecha_vencimiento' => $request['attr_fecha_vencimiento'] ?? false,
                'attr_lote_produccion' => $request['attr_lote_produccion'] ?? false,
                'attr_venta_menudeo' => $request['attr_venta_menudeo'] ?? false,
                'codigo_barras' => $request['cb'] ?? null,
                'registro_sanitario' => $request['registro_sanitario'] ?? null,
                'presentacion_modelo' => $request['presentacion_modelo'] ?? null,
                'concentracion_detalle' => $request['concentracion_detalle'] ?? null,
                'cantidad' => $request['cantidad'] ?? 0,
                'precio_compra' => $request['precio_compra'] ?? null,
                'pvp' => $request['pvp'] ?? null,
                'pv_docena' => $request['pv_docena'] ?? null,
                'pvp_dto' => $request['pvp_dto'] ?? null,
                'pvc' => $request['pvc'] ?? null,
                'pvc_dto' => $request['pvc_dto'] ?? null,
                'costo_operativo' => $request['costo_operativo'] ?? 0,
                'peso' => $request['peso'] ?? 0,
            ];

            $producto = Producto::create($productoData);

            // Crear líneas
            foreach ($lines as $ln) {
                // normalizar campos de la línea
                $lineData = [
                    'producto_id' => $producto->id,
                    'cb' => $ln['cb'] ?? null,
                    'codigo_ref' => $ln['codigo_ref'] ?? null,
                    'presentacion' => $ln['presentacion'] ?? null,
                    'concentracion' => $ln['concentracion'] ?? null,
                    'cantidad' => isset($ln['cantidad']) ? (int)$ln['cantidad'] : 0,
                    'precio_compra' => isset($ln['precio_compra']) && $ln['precio_compra'] !== '' ? $ln['precio_compra'] : null,
                    'pvp' => isset($ln['pvp']) && $ln['pvp'] !== '' ? $ln['pvp'] : null,
                    'pvp_dto' => isset($ln['pvp_dto']) && $ln['pvp_dto'] !== '' ? $ln['pvp_dto'] : null,
                    'peso' => isset($ln['peso']) && $ln['peso'] !== '' ? $ln['peso'] : null,
                    'pa1' => $ln['pa1'] ?? null,
                    'pa2' => $ln['pa2'] ?? null,
                    'lote' => $ln['lote'] ?? null,
                    'fecha_venc' => !empty($ln['fecha_venc']) ? $ln['fecha_venc'] : null,
                ];

                ProductoLinea::create($lineData);
            }

            DB::commit();

            // return redirect()->route('compras.create')
            //     ->with('success', 'Producto creado correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            // registra el error en logs y vuelve con mensaje
            Log::error('Error creando producto: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->withInput()->withErrors(['general' => 'Ocurrió un error al guardar el producto.']);
        }
    }

    // app/Http/Controllers/ProductApiController.php
    public function search(Request $request)
    {
        $q = $request->get('q', null);
        $cb = $request->get('cb', null);
        $ref = $request->get('ref', null);

        // Cargamos las lineas ordenadas por created_at desc (la más reciente primero)
        $query = Producto::with(['lineas' => function ($q) {
            $q->orderBy('created_at', 'desc');
        }]);

        if ($cb) {
            $query->where('codigo_barras', 'like', "%{$cb}%");
        }

        if ($ref) {
            $query->where('codigo_ref', 'like', "%{$ref}%");
        }

        if ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('nombre', 'like', "%{$q}%")
                    ->orWhere('presentacion_modelo', 'like', "%{$q}%")
                    ->orWhere('concentracion_detalle', 'like', "%{$q}%");
            });
        }

        // Optional filters (adaptar según esquema)
        if ($request->boolean('stock_min')) {
            // ejemplo, si tu tabla tiene stock...
            // if (Schema::hasColumn('productos', 'stock')) {
            //     $query->whereColumn('stock', '<=', 'stock_min');
            // }
        }

        if ($request->boolean('obsoletos')) {
            // if (Schema::hasColumn('productos', 'obsoleto')) {
            //     $query->where('obsoleto', 1);
            // }
        }

        $items = $query->orderBy('nombre')->limit(100)->get();

        $result = $items->map(function ($p) {
            // Tomamos la primera linea (la más reciente por el orderBy anterior)
            $linea = $p->lineas->first();

            // Si no hay linea, fallback a los campos en el producto
            $precio_compra = $linea->precio_compra ?? $p->precio_compra ?? null;
            $pvp = $linea->pvp ?? $p->pvp ?? null;

            return [
                'id' => $p->id,
                'cb' => $p->codigo_barras,
                'codigo_ref' => $p->codigo_ref ?? '',
                'nombre' => $p->nombre,
                'presentacion' => $p->presentacion_modelo,
                'concentracion' => $p->concentracion_detalle,
                'familia' => $p->familia ? $p->familia->nombre : null,
                // precios tomados desde la relacion
                'precio_compra' => $precio_compra !== null ? (float)$precio_compra : null,
                'pvp' => $pvp !== null ? (float)$pvp : null,
                // Si quieres exponer la linea completa:
                'precio_linea' => $linea ? [
                    'id' => $linea->id,
                    'precio_compra' => $linea->precio_compra !== null ? (float)$linea->precio_compra : null,
                    'pvp' => $linea->pvp !== null ? (float)$linea->pvp : null,
                ] : null,
                'unidad_medida_id' => $p->unidad_medida_id ?? null,
            ];
        });

        return response()->json(array_values($result->toArray()));
    }
}
