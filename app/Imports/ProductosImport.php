<?php

namespace App\Imports;

use App\Models\Producto;
use App\Models\ProductoLinea;
use App\Models\Marca;
use App\Models\Familia;
use App\Models\Laboratorio;
use App\Models\UnidadMedida;
use App\Models\AlmacenIngreso;
use App\Models\AlmacenIngresoDetalle;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductosImport implements ToCollection, WithHeadingRow
{
    private function parseNum($val) {
        if ($val === null || $val === "") return 0;
        // Si viene como string con coma (ej: 14,5), lo convertimos a punto
        if (is_string($val)) {
            $val = str_replace(',', '.', $val);
        }
        return (float) $val;
    }

    private function parseDate($val) {
        if (empty($val)) return null;
        try {
            if (is_numeric($val)) {
                // Posible fecha de Excel serializada
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($val)->format('Y-m-d');
            }
            return date('Y-m-d', strtotime($val));
        } catch (\Exception $e) {
            return null;
        }
    }

    public function collection(Collection $rows)
    {
        Log::info('Import started. Initial Rows Count: ' . $rows->count());
        
        DB::beginTransaction();
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            
            // Crear un solo ingreso para todo el lote importado
            $ingreso = AlmacenIngreso::create([
                'company_id' => $user->company_id,
                'empresa_id' => $user->company_id,
                'user_id' => $user->id,
                'sucursal_id' => $user->branch_id,
                'fecha' => now(),
                'observacion' => 'Importación masiva desde Excel ' . date('Y-m-d H:i:s'),
            ]);

            Log::info('Ingreso created ID: ' . $ingreso->id);

            // Mapeo flexible de columnas para manejar truncamientos o variaciones
            $aliases = [
                'codigo_barras' => ['codigo_barras', 'barcode', 'codigo_barra', 'ean', 'sku'],
                'nombre'        => ['nombre', 'name', 'producto', 'product'],
                'marca'         => ['marca', 'brand'],
                'familia'       => ['familia', 'category', 'family'],
                'laboratorio'   => ['laboratorio', 'lab', 'laboratory', 'laboratoi'],
                'unidad_medida' => ['unidad_medida', 'uom', 'unidad', 'unidad_m'],
                'tipo_impuesto' => ['tipo_impuesto', 'tax', 'impuesto', 'tipo_impu'],
                'precio_compra' => ['precio_compra', 'cost', 'costo', 'precio_co'],
                'pvp'           => ['pvp'],
                'pvpd'          => ['pvpd', 'pvp_dto'],
                'pvc'           => ['pvc'],
                'pvcd'          => ['pvcd', 'pvc_dto'],
                'presentacion'  => ['presentacion', 'presentation', 'formato'],
                'concentracion' => ['concentracion', 'concentration', 'detalle'],
                'fecha_vencimiento' => ['fecha_vencimiento', 'vencimiento', 'exp_date', 'vence'],
                'stock_inicial' => ['stock_inicial', 'stock', 'cantidad', 'qty'],
            ];

            foreach ($rows as $index => $rowArray) {
                $row = $rowArray->toArray();
                Log::info('Processing row ' . ($index + 1) . ': ' . json_encode($row));

                // Extraer datos usando los alias
                $r = [];
                foreach ($aliases as $field => $fieldAliases) {
                    foreach ($fieldAliases as $alias) {
                        if (array_key_exists($alias, $row)) {
                            $r[$field] = $row[$alias];
                            break;
                        }
                    }
                    if (!isset($r[$field])) $r[$field] = null;
                }

                // Si el nombre está vacío, saltamos la fila
                if (empty($r['nombre'])) {
                    Log::warning('Row ' . ($index + 1) . ' skipped: Nombre is empty.');
                    continue;
                }

                $codigoBarras = trim($r['codigo_barras'] ?? '');
                
                // 1. Manejar Relaciones (Nombre -> ID)
                $marcaId = $this->getOrCreateMarca($r['marca']);
                $familiaId = $this->getOrCreateFamilia($r['familia']);
                $labId = $this->getOrCreateLaboratorio($r['laboratorio']);
                $unidadId = $this->getOrCreateUnidad($r['unidad_medida']);

                // 2. Buscar si el producto existe por código de barras o crear
                $linea = null;
                $producto = null;

                if ($codigoBarras !== "") {
                    $linea = ProductoLinea::where('cb', $codigoBarras)->first();
                }
                
                if ($linea) {
                    $producto = $linea->producto;
                } else {
                    $producto = Producto::create([
                        'id_empresa' => $user->company_id,
                        'nombre' => $r['nombre'],
                        'laboratorio' => $labId,
                        'familia_id' => $familiaId,
                        'marca_id' => $marcaId,
                        'unidad_medida_id' => $unidadId,
                        'tipo_impuesto' => strtolower($r['tipo_impuesto'] ?? 'gravado'),
                        'condicion_venta' => 'LIBRE',
                        'codigo_barras' => $codigoBarras,
                        'presentacion_modelo' => $r['presentacion'],
                        'concentracion_detalle' => $r['concentracion'],
                        'attr_fecha_vencimiento' => !empty($r['fecha_vencimiento']),
                    ]);

                    $linea = ProductoLinea::create([
                        'producto_id' => $producto->id,
                        'cb' => $codigoBarras,
                        'cantidad' => 0,
                        'presentacion' => $r['presentacion'],
                        'concentracion' => $r['concentracion'],
                        'fecha_venc' => !empty($r['fecha_vencimiento']) ? $this->parseDate($r['fecha_vencimiento']) : null,
                        'precio_compra' => $this->parseNum($r['precio_compra']),
                        'pvp' => $this->parseNum($r['pvp']),
                        'pvp_dto' => $this->parseNum($r['pvpd'] ?? null),
                        'pvc' => $this->parseNum($r['pvc']),
                        'pvc_dto' => $this->parseNum($r['pvcd'] ?? null),
                    ]);
                }

                // 3. Crear Detalle de Ingreso (Stock)
                $cantidad = $this->parseNum($r['stock_inicial']);
                if ($cantidad > 0) {
                    AlmacenIngresoDetalle::create([
                        'ingreso_id' => $ingreso->id,
                        'producto_id' => $producto->id,
                        'producto_linea_id' => $linea->id,
                        'cantidad' => $cantidad,
                        'costo' => $this->parseNum($r['precio_compra']),
                        'cop' => 0,
                        'mu' => 0,
                        'mud' => 0,
                        'mup' => 0,
                        'pvp' => $this->parseNum($r['pvp']),
                        'pvpd' => $this->parseNum($r['pvpd'] ?? $r['pvp']),
                        'pvc' => $this->parseNum($r['pvc']),
                        'pvcd' => $this->parseNum($r['pvcd'] ?? $r['pvc']),
                        'stock_min' => 0,
                        'stock_max' => 0,
                        'lote' => 'IMP-' . date('Ymd'),
                        'fecha_vencimiento' => !empty($r['fecha_vencimiento']) ? $this->parseDate($r['fecha_vencimiento']) : null,
                    ]);
                    Log::info('Created Stock Entry for Product: ' . $producto->nombre);
                }
                
                Log::info('Processed successfully Product ID: ' . $producto->id);
            }

            DB::commit();
            Log::info('Import transaction committed.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Import error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            throw $e;
        }
    }

    private function getOrCreateMarca($name) {
        if (empty($name)) return null;
        $name = trim($name);
        $marca = Marca::where('nombre', $name)->first();
        if (!$marca) {
            $marca = Marca::create([
                'nombre' => $name,
                'company_id' => Auth::user()->company_id
            ]);
        }
        return $marca->id;
    }

    private function getOrCreateFamilia($name) {
        if (empty($name)) return null;
        $name = trim($name);
        $familia = Familia::where('nombre', $name)->first();
        if (!$familia) {
            $familia = Familia::create([
                'nombre' => $name, 
                'id_empresa' => Auth::user()->company_id,
                'company_id' => Auth::user()->company_id
            ]);
        }
        return $familia->id;
    }

    private function getOrCreateLaboratorio($name) {
        if (empty($name)) return null;
        $name = trim($name);
        $lab = Laboratorio::where('nombre', $name)->first();
        if (!$lab) {
            $lab = Laboratorio::create([
                'nombre' => $name,
                'company_id' => Auth::user()->company_id
                ]);
        }
        return $lab->id;
    }

    private function getOrCreateUnidad($name) {
        if (empty($name)) return null;
        $name = trim($name);
        $unidad = UnidadMedida::where('nombre', $name)
            ->orWhere('codigo', $name)
            ->first();
        if (!$unidad) {
            $unidad = UnidadMedida::create([
                'nombre' => $name,
                'codigo' => substr($name, 0, 3),
                'company_id' => Auth::user()->company_id
            ]);
        }
        return $unidad->id;
    }
}
