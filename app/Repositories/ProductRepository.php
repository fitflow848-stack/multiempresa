<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class ProductRepository
{
    /**
     * Buscar productos (reemplaza el DB::select del controller).
     */
    /**
     * Buscar productos (reemplaza el DB::select del controller).
     */
    public function buscar(string $q, ?int $sucursalId = null): array
    {
        $sucursalId = $sucursalId ?? session('active_branch_id');
        $joinIngresos = $sucursalId ? "INNER JOIN almacen_ingresos ai ON ai.id = ad.ingreso_id AND ai.sucursal_id = ?" : "";
        $params = ["%{$q}%", "%{$q}%"];
        if ($sucursalId) {
            array_unshift($params, $sucursalId); // Goes into the join
        }

        return DB::select("
            SELECT
                p.id AS producto_id,
                MAX(p.tipo_impuesto) as tipo_impuesto,
                ad.producto_linea_id AS product_linea_id,
                MAX(ad.id) AS id,
                CONCAT_WS(' / ', 
                    MAX(p.nombre), 
                    NULLIF(CONCAT_WS(' ', 
                        NULLIF(NULLIF(TRIM(MAX(pl.presentacion)), ''), '-- Ver --'),
                        NULLIF(NULLIF(TRIM(MAX(pl.concentracion)), ''), '-- Ver --')
                    ), '')
                ) AS nombre,
                CONCAT(
                    'lt. ', MAX(ad.lote), ' Fv. ', LPAD(DAY(MAX(ad.fecha_vencimiento)), 2, '0'),
                    ' ', LOWER(LEFT(MONTHNAME(MAX(ad.fecha_vencimiento)), 3)), ' ', RIGHT(YEAR(MAX(ad.fecha_vencimiento)), 2)
                ) AS detalle,
                MAX(m.nombre) AS marca,
                MAX(f.nombre) AS familia,
                MAX(um.nombre) AS unidad_medida,
                MAX(p.ficha_tecnica) AS ficha_tecnica,
                MAX(p.almacenamiento) AS almacenamiento,
                MAX(p.codigo_barras) AS codigo_barras,
                SUM(ad.cantidad) AS cantidad_total,
                MAX(ad.costo) AS costo,
                MAX(ad.pvp) AS pvp,
                MAX(ad.pvpd) AS pvpd,
                MAX(ad.pvc) AS pvc,
                MAX(ad.pvcd) AS pvcd,
                COUNT(ad.id) AS total_lotes,
                MAX(ad.fecha_vencimiento) as fecha_vencimiento,
                MAX(ad.stock_min) AS stock_min,
                CASE 
                    WHEN SUM(ad.cantidad) <= MAX(COALESCE(ad.stock_min, 0)) AND MAX(COALESCE(ad.stock_min, 0)) > 0 
                    THEN 1 
                    ELSE 0 
                END AS stock_bajo
            FROM almacen_ingreso_detalle ad
            $joinIngresos
            INNER JOIN productos p ON p.id = ad.producto_id
            INNER JOIN producto_lineas pl ON pl.id = ad.producto_linea_id 
            LEFT JOIN marcas m ON m.id = p.marca_id
            LEFT JOIN familias f ON f.id = p.familia_id
            LEFT JOIN unidades_medida um ON um.id = p.unidad_medida_id
            WHERE (p.nombre LIKE ? OR p.codigo_barras LIKE ?) AND ad.cantidad > 0
            GROUP BY p.id, ad.producto_linea_id
            ORDER BY MAX(p.nombre) ASC
        ", $params);
    }

    public function obtenerLotes(int $productoId, ?int $sucursalId = null): array
    {
        $sucursalId = $sucursalId ?? session('active_branch_id');
        
        $joinIngresos = $sucursalId ? "INNER JOIN almacen_ingresos ai ON ai.id = ad.ingreso_id AND ai.sucursal_id = ?" : "";
        $params = $sucursalId ? [$sucursalId, $productoId] : [$productoId];
        
        return DB::select("SELECT
                    ad.id,
                    CONCAT('LOTE-', ad.id) as lote,
                    NULL as fecha_vencimiento,
                    ad.cantidad,
                    ad.pvp,
                    ad.pvc,
                    CONCAT('Stock: ', ad.cantidad) as descripcion_lote
                FROM almacen_ingreso_detalle ad
                $joinIngresos
                WHERE ad.producto_id = ? AND ad.cantidad > 0
                ORDER BY ad.id ASC", $params);
    }

    public function obtenerProductoConLotes(int $productoId)
    {
        return DB::selectOne("SELECT p.* FROM productos p WHERE p.id = ?", [$productoId]);
    }


    public function elegirStock(int $productoId, ?int $sucursalId = null): array
    {
        $sucursalId = $sucursalId ?? session('active_branch_id');
        $joinIngresos = $sucursalId ? "INNER JOIN almacen_ingresos ai ON ai.id = ad.ingreso_id AND ai.sucursal_id = ?" : "";
        $params = $sucursalId ? [$sucursalId, $productoId] : [$productoId];

        return DB::select("SELECT
                    ad.id,
                    ad.producto_linea_id,
                    CONCAT('LOTE-', ad.id) as lote,
                    ad.fecha_vencimiento as fecha_vencimiento,
                    ad.cantidad,
                    ad.pvp,
                    ad.pvc,
                    ad.fecha_vencimiento as fecha_formato,
                    'ONIU' as empaque,
                    ad.cantidad as unidades
                FROM
                    almacen_ingreso_detalle ad
                $joinIngresos
                WHERE ad.producto_id = ? AND ad.cantidad > 0
                ORDER BY ad.id ASC", $params);
    }
}
