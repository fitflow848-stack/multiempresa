<?php

namespace App\Http\Controllers;

use App\Models\Concentracion;
use App\Models\Marca;
use App\Models\Presentacion;
use App\Models\UnidadMedida;
use App\Models\Laboratorio;
use App\Models\Producto;
use App\Models\ProductoLinea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{
    public function step1()
    {
        $marcas = Marca::all();
        $unidades = UnidadMedida::all();
        $laboratorios = Laboratorio::all();
        $presentaciones = Presentacion::activo()->orderBy('nombre')->get();
        $concentraciones = Concentracion::activo()->orderBy('nombre')->get();

        return view('productos.create', compact('marcas', 'unidades', 'laboratorios', 'presentaciones', 'concentraciones'));
    }

    public function step2(Request $request)
    {
        // Validación completa de todos los campos que vienen del formulario
        $data = $request->validate([
            'laboratorio_id' => ['nullable', 'integer'],
            'familia_id' => ['nullable', 'integer'],
            'subfamilia_id' => ['nullable', 'integer'],
            'nombre' => ['required', 'string', 'max:1000'],
            'marca_id' => ['nullable', 'integer'],
            'unidad_medida_id' => ['nullable', 'integer'],
            'tipo_impuesto' => ['nullable', 'string'],
            'condicion_venta' => ['nullable', 'string'],
            'codigo_personalizado' => ['nullable', 'string'],
            'notas' => ['nullable', 'string'],
            // 'opciones_avanzadas' => ['nullable', 'boolean'],
            // 'attr_numero_serie' => ['nullable', 'boolean'],
            // 'attr_fecha_vencimiento' => ['nullable', 'boolean'],
            // 'attr_lote_produccion' => ['nullable', 'boolean'],
            // 'attr_venta_menudeo' => ['nullable', 'boolean'],
            // Campos de características
            'propiedades' => ['nullable', 'array'],
            'propiedades.*' => ['nullable', 'string'],
            'almacenamiento' => ['nullable', 'array'],
            'almacenamiento.*' => ['nullable', 'string'],
            'seguridad' => ['nullable', 'array'],
            'seguridad.*' => ['nullable', 'string'],
            // Campos de ficha técnica
            'ficha_tecnica' => ['nullable', 'array'],
            'ficha_tecnica.*' => ['nullable', 'string'],
            // Campos de imágenes
            'imagen_alt' => ['nullable', 'string'],
            'imagen_titulo' => ['nullable', 'string'],
            'imagen_fuente' => ['nullable', 'string'],
            // Archivos de imagen
            'imagen_principal' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            'imagenes_adicionales.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
        ]);

        // Manejar imágenes subidas en step1
        $uploadedImages = [];

        // Imagen principal
        if ($request->hasFile('imagen_principal')) {
            $file = $request->file('imagen_principal');
            $filename = time() . '_principal_' . $file->getClientOriginalName();

            // Guardar en storage/app/public/productos/temp
            $path = $file->storeAs('productos/temp', $filename, 'public');

            $data['imagen_principal_temp'] = $filename;
            $uploadedImages['principal'] = [
                'name' => $filename,
                'original_name' => $file->getClientOriginalName(),
                'temp_path' => $path
            ];
        }

        // Imágenes adicionales
        if ($request->hasFile('imagenes_adicionales')) {
            $imagenesAdicionales = [];
            foreach ($request->file('imagenes_adicionales') as $index => $file) {
                $filename = time() . '_adicional_' . $index . '_' . $file->getClientOriginalName();

                // Guardar en storage/app/public/productos/temp
                $path = $file->storeAs('productos/temp', $filename, 'public');

                $imagenesAdicionales[] = $filename;
                $uploadedImages['adicional_' . $index] = [
                    'name' => $filename,
                    'original_name' => $file->getClientOriginalName(),
                    'temp_path' => $path
                ];
            }
            $data['imagenes_adicionales_temp'] = $imagenesAdicionales;
        }

        // Agregar información de imágenes subidas
        $data['uploaded_images'] = $uploadedImages;

        // Normalizar booleans que vengan como "on"
        foreach (['opciones_avanzadas', 'attr_numero_serie', 'attr_fecha_vencimiento', 'attr_lote_produccion', 'attr_venta_menudeo'] as $b) {
            if ($request->has($b)) {
                $data[$b] = in_array($request->input($b), ['1', 'true', 'on', 1, true], true) ? 1 : 0;
            } else {
                $data[$b] = 0;
            }
        }

        // Pasar todos los datos a la vista detallada (step2)
        return view('productos.create-detailed', [
            'producto_data' => $data,
            'presentaciones' => Presentacion::activo()->orderBy('nombre')->get(),
            'concentraciones' => Concentracion::activo()->orderBy('nombre')->get()
        ]);
    }

    public function create()
    {
        // Alias para step1 para mantener compatibilidad con rutas estándar
        return $this->step1();
    }

    /**
     * Guardar producto y sus líneas
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            // Manejar subida de imagen principal usando Storage
            $imagenPrincipal = null;
            if ($request->hasFile('imagen_principal')) {
                // Imagen nueva subida directamente en step2
                $file = $request->file('imagen_principal');
                $filename = time() . '_' . $file->getClientOriginalName();

                // Guardar en storage/app/public/productos
                $path = $file->storeAs('productos', $filename, 'public');
                $imagenPrincipal = $path;
            } elseif ($request->input('imagen_principal_temp')) {
                // Imagen que viene desde step1 (archivo temporal)
                $tempFilename = $request->input('imagen_principal_temp');

                // Verificar si existe el archivo temporal
                if (Storage::disk('public')->exists('productos/temp/' . $tempFilename)) {
                    // Mover del directorio temporal al final
                    $finalFilename = $tempFilename;

                    // Copiar archivo del temp al directorio final
                    Storage::disk('public')->copy(
                        'productos/temp/' . $tempFilename,
                        'productos/' . $finalFilename
                    );

                    // Eliminar archivo temporal
                    Storage::disk('public')->delete('productos/temp/' . $tempFilename);

                    $imagenPrincipal = 'productos/' . $finalFilename;
                }
            }

            // Manejar imágenes adicionales usando Storage
            $imagenesAdicionales = [];
            if ($request->hasFile('imagenes_adicionales')) {
                // Imágenes nuevas subidas directamente
                foreach ($request->file('imagenes_adicionales') as $file) {
                    if ($file) {
                        $filename = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();

                        // Guardar en storage/app/public/productos
                        $path = $file->storeAs('productos', $filename, 'public');
                        $imagenesAdicionales[] = $path;
                    }
                }
            } elseif ($request->input('imagenes_adicionales_temp')) {
                // Imágenes que vienen desde step1 (archivos temporales)
                $tempFilenames = $request->input('imagenes_adicionales_temp');
                if (is_array($tempFilenames)) {
                    foreach ($tempFilenames as $tempFilename) {
                        // Verificar si existe el archivo temporal
                        if (Storage::disk('public')->exists('productos/temp/' . $tempFilename)) {
                            $finalFilename = $tempFilename;

                            // Copiar archivo del temp al directorio final
                            Storage::disk('public')->copy(
                                'productos/temp/' . $tempFilename,
                                'productos/' . $finalFilename
                            );

                            // Eliminar archivo temporal
                            Storage::disk('public')->delete('productos/temp/' . $tempFilename);

                            $imagenesAdicionales[] = 'productos/' . $finalFilename;
                        }
                    }
                }
            }

            // Preparar datos de características como JSON
            $propiedades = [];
            if ($request->has('propiedades')) {
                $propiedades = array_filter($request->input('propiedades', []), function ($value) {
                    return !is_null($value) && $value !== '';
                });
            }

            $almacenamiento = [];
            if ($request->has('almacenamiento')) {
                $almacenamiento = array_filter($request->input('almacenamiento', []), function ($value) {
                    return !is_null($value) && $value !== '';
                });
            }

            $seguridad = [];
            if ($request->has('seguridad')) {
                $seguridad = array_filter($request->input('seguridad', []), function ($value) {
                    return !is_null($value) && $value !== '';
                });
            }

            $fichatecnica = [];
            if ($request->has('ficha_tecnica')) {
                $fichatecnica = array_filter($request->input('ficha_tecnica', []), function ($value) {
                    return !is_null($value) && $value !== '';
                });
            }

            // Combinar todas las características en un solo JSON
            $caracteristicas = json_encode([
                'propiedades' => $propiedades,
                'almacenamiento' => $almacenamiento,
                'seguridad' => $seguridad,
            ]);

            $productoData = [
                'id_empresa' => Auth::user()->company_id,
                'nombre' => $request->input('nombre'),
                'laboratorio' => $request->input('laboratorio_id'),
                'familia_id' => $request->input('familia_id'),
                'subfamilia_id' => $request->input('subfamilia_id'),
                'marca_id' => $request->input('marca_id'),
                'unidad_medida_id' => $request->input('unidad_medida_id'),
                'tipo_impuesto' => $request->input('tipo_impuesto'),
                'condicion_venta' => $request->input('condicion_venta'),
                // Campos booleanos
                'opciones_avanzadas' => $request->boolean('opciones_avanzadas'),
                'attr_numero_serie' => $request->boolean('attr_numero_serie'),
                'attr_fecha_vencimiento' => $request->boolean('attr_fecha_vencimiento'),
                'attr_lote_produccion' => $request->boolean('attr_lote_produccion'),
                'attr_venta_menudeo' => $request->boolean('attr_venta_menudeo'),
                // Campos JSON
                'caracteristicas' => $caracteristicas,
                'ficha_tecnica' => json_encode($fichatecnica),
                // Campos de imágenes
                'imagen_principal' => $imagenPrincipal,
                'imagenes_adicionales' => json_encode($imagenesAdicionales),
                'imagen_alt' => $request->input('imagen_alt'),
                'imagen_titulo' => $request->input('imagen_titulo'),
                'imagen_fuente' => $request->input('imagen_fuente'),
            ];

            $producto = Producto::create($productoData);

            // Si el formulario viene desde step2 (detailed), crear líneas
            $linesJson = $request->input('product_lines', '[]');
            $lines = json_decode($linesJson, true);

            if (is_array($lines) && count($lines) > 0) {
                // Crear líneas
                foreach ($lines as $ln) {
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
                        'stock_maximo' => isset($ln['stock_maximo']) ? (int)$ln['stock_maximo'] : null,
                        'stock_minimo' => isset($ln['stock_minimo']) ? (int)$ln['stock_minimo'] : null,
                        'pv_docena' => isset($ln['pv_docena']) && $ln['pv_docena'] !== '' ? $ln['pv_docena'] : null,
                        'pvc' => isset($ln['pvc']) && $ln['pvc'] !== '' ? $ln['pvc'] : null,
                        'pvc_dto' => isset($ln['pvc_dto']) && $ln['pvc_dto'] !== '' ? $ln['pvc_dto'] : null,
                        'pvp2' => isset($ln['pvp2']) && $ln['pvp2'] !== '' ? $ln['pvp2'] : null,
                    ];

                    ProductoLinea::create($lineData);
                }
            }

            DB::commit();

            // Debug: verificar si hay sesión return_to_compras
            Log::info('ProductoController store - return_to_compras session:', ['session_value' => session('return_to_compras')]);

            // Si viene desde compras, preparar redirección especial
            if (session('return_to_compras')) {
                Log::info('ProductoController store - Detectado return_to_compras, preparando redirección especial');
                session()->forget('return_to_compras');

                if (request()->ajax()) {
                    // Para peticiones AJAX, devolver JSON con URL de redirección y parámetros
                    $redirectUrl = route('compras.create') . '?restore_compra_data=true&new_product_id=' . $producto->id;
                    Log::info('ProductoController store - AJAX redirect URL:', ['url' => $redirectUrl]);
                    return response()->json([
                        'success' => true,
                        'message' => 'Producto creado correctamente y agregado a la compra.',
                        'redirect' => $redirectUrl
                    ]);
                } else {
                    // Para peticiones normales, redirect tradicional
                    Log::info('ProductoController store - Normal redirect to compras.create');
                    return redirect()->route('compras.create')
                        ->with('success', 'Producto creado correctamente y agregado a la compra.')
                        ->with('new_product_id', $producto->id)
                        ->with('restore_compra_data', true);
                }
            }

            Log::info('ProductoController store - No return_to_compras session found, redirecting to compras.index');

            if (request()->ajax()) {
                // Para peticiones AJAX normales
                return response()->json([
                    'success' => true,
                    'message' => 'Producto creado correctamente.',
                    'redirect' => route('compras.index')
                ]);
            }

            return redirect()->route('compras.index')
                ->with('success', 'Producto creado correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error creando producto: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->withInput()->withErrors(['general' => 'Ocurrió un error al guardar el producto: ' . $e->getMessage()]);
        }
    }

    // app/Http/Controllers/ProductApiController.php
    public function search(Request $request)
    {
        $q = $request->get('q', null);
        $cb = $request->get('cb', null);
        $ref = $request->get('ref', null);

        // Buscamos desde ProductoLinea con el producto relacionado
        $query = ProductoLinea::with(['producto'])->orderBy('created_at', 'desc');

        if ($cb) {
            $query->where('cb', 'like', "%{$cb}%");
        }

        if ($ref) {
            $query->where('codigo_ref', 'like', "%{$ref}%");
        }

        if ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('presentacion', 'like', "%{$q}%")
                    ->orWhere('concentracion', 'like', "%{$q}%")
                    ->orWhereHas('producto', function ($productQuery) use ($q) {
                        $productQuery->where('nombre', 'like', "%{$q}%")
                            ->orWhere('presentacion_modelo', 'like', "%{$q}%")
                            ->orWhere('concentracion_detalle', 'like', "%{$q}%");
                    });
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

        $lineas = $query->limit(100)->get();

        $result = $lineas->map(function ($linea) {
            $producto = $linea->producto;
            return [
                'id' => $producto->id,
                'linea_id' => $linea->id,
                'cb' => $linea->cb ?? $producto->codigo_barras,
                'codigo_ref' => $linea->codigo_ref ?? '',
                'nombre' => $producto->nombre,
                'presentacion' => $linea->presentacion ?? $producto->presentacion_modelo,
                'concentracion' => $linea->concentracion ?? $producto->concentracion_detalle,
                'familia' => $producto->familia ? $producto->familia->nombre : null,
                // precios tomados desde la línea
                'precio_compra' => $linea->precio_compra !== null ? (float)$linea->precio_compra : null,
                'pvp' => $linea->pvp !== null ? (float)$linea->pvp : null,
                // Campos de stock, lote y fecha de vencimiento
                'stock_min' => $linea->stock_minimo ?? $producto->stock_min ?? 0,
                'stock_max' => $linea->stock_maximo ?? $producto->stock_max ?? 0,
                'lote' => $linea->lote ?? '',
                'fecha_vencimiento' => $linea->fecha_venc ? \Carbon\Carbon::parse($linea->fecha_venc)->format('Y-m-d') : null,
                // Información completa de la línea
                'precio_linea' => [
                    'id' => $linea->id,
                    'precio_compra' => $linea->precio_compra !== null ? (float)$linea->precio_compra : null,
                    'pvp' => $linea->pvp !== null ? (float)$linea->pvp : null,
                    'pvp_dto' => $linea->pvp_dto !== null ? (float)$linea->pvp_dto : null,
                    'cantidad' => $linea->cantidad,
                    'peso' => $linea->peso,
                ],
                'unidad_medida_id' => $producto->unidad_medida_id ?? null,
            ];
        });

        return response()->json(array_values($result->toArray()));
    }

    /**
     * Obtener producto por ID para API
     */
    public function getById($id)
    {
        try {
            $producto = Producto::with(['lineas'])->find($id);

            if (!$producto) {
                return response()->json(['error' => 'Producto no encontrado'], 404);
            }
            $result =  [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'unidad_medida_id' => $producto->unidad_medida_id ?? null,
                'cb' => $linea->cb ?? $producto->codigo_barras,
                'familia' => $producto->familia ? $producto->familia->nombre : null,
                'lineas' => $producto->lineas->map(function ($linea) {
                    return [
                        'linea_id' => $linea->id,
                        'codigo_ref' => $linea->codigo_ref ?? '',
                        'presentacion' => $linea->presentacion,
                        'concentracion' => $linea->concentracion,
                        // precios tomados desde la línea
                        'precio_compra' => $linea->precio_compra !== null ? (float)$linea->precio_compra : null,
                        'pvp' => $linea->pvp !== null ? (float)$linea->pvp : null,
                        // Campos de stock, lote y fecha de vencimiento
                        'stock_min' => $linea->stock_minimo ?? $producto->stock_min ?? 0,
                        'stock_max' => $linea->stock_maximo ?? $producto->stock_max ?? 0,
                        'lote' => $linea->lote ?? '',
                        'fecha_vencimiento' => $linea->fecha_venc ? \Carbon\Carbon::parse($linea->fecha_venc)->format('Y-m-d') : null,
                    ];
                })
            ];
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }
}
