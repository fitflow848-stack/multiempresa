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
    public function buscar(string $q, ?int $sucursalId = null, bool $includeEmpty = false): array
    {
        $sucursalId = $sucursalId ?? session('active_branch_id');
        $companyId = session('active_company_id') ?? (auth()->check() ? auth()->user()->company_id : null);
        
        $joinIngresos = "INNER JOIN almacen_ingresos ai ON ai.id = ad.ingreso_id AND ai.company_id = ?";
        if ($sucursalId) {
            $joinIngresos .= " AND ai.sucursal_id = ?";
        }
        
        $params = [$companyId];
        if ($sucursalId) {
            $params[] = $sucursalId;
        }
        $params[] = "%{$q}%";
        $params[] = "%{$q}%";

        $having = $includeEmpty ? "" : "HAVING SUM(ad.cantidad) > 0";

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
                MAX(p.imagen_principal) AS imagen_principal,
                SUM(ad.cantidad) AS cantidad_total,
                MAX(pl.precio_compra) AS costo,
                MAX(ad.pvp) AS pvp,
                MAX(ad.pvpd) AS pvpd,
                MAX(ad.pvc) AS pvc,
                MAX(ad.pvcd) AS pvcd,
                MAX(p.pv_docena) AS pv_docena,
                COUNT(ad.id) AS total_lotes,
                MAX(ad.fecha_vencimiento) as fecha_vencimiento,
                MAX(ad.stock_min) AS stock_min,
                MAX(ad.lote) AS lote,
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
            WHERE (p.nombre LIKE ? OR p.codigo_barras LIKE ?)
            GROUP BY p.id, ad.producto_linea_id
            $having
            ORDER BY MAX(p.nombre) ASC
        ", $params);
    }

    public function obtenerLotes(int $productoId, ?int $sucursalId = null): array
    {
        $sucursalId = $sucursalId ?? session('active_branch_id');
        $companyId = session('active_company_id') ?? (auth()->check() ? auth()->user()->company_id : null);
        
        $joinIngresos = "INNER JOIN almacen_ingresos ai ON ai.id = ad.ingreso_id AND ai.company_id = ?";
        if ($sucursalId) {
            $joinIngresos .= " AND ai.sucursal_id = ?";
        }
        
        $params = [$companyId];
        if ($sucursalId) {
            $params[] = $sucursalId;
        }
        $params[] = $productoId;
        
        return DB::select("SELECT
                    MAX(ad.id) as id,
                    COALESCE(ad.lote, CONCAT('LOTE-', MAX(ad.id))) as lote,
                    ad.fecha_vencimiento,
                    ad.pvp,
                    ad.pvc,
                    SUM(ad.cantidad) as cantidad,
                    CONCAT('Stock: ', SUM(ad.cantidad)) as descripcion_lote
                FROM almacen_ingreso_detalle ad
                $joinIngresos
                WHERE ad.producto_id = ?
                GROUP BY ad.lote, ad.fecha_vencimiento, ad.pvp, ad.pvc
                HAVING SUM(ad.cantidad) > 0
                ORDER BY MAX(ad.id) ASC", $params);
    }

    public function obtenerProductoConLotes(int $productoId)
    {
        return DB::selectOne("SELECT p.* FROM productos p WHERE p.id = ?", [$productoId]);
    }


    public function elegirStock(int $productoId, ?int $sucursalId = null): array
    {
        $sucursalId = $sucursalId ?? session('active_branch_id');
        $companyId = session('active_company_id') ?? (auth()->check() ? auth()->user()->company_id : null);
        
        $joinIngresos = "INNER JOIN almacen_ingresos ai ON ai.id = ad.ingreso_id AND ai.company_id = ?";
        if ($sucursalId) {
            $joinIngresos .= " AND ai.sucursal_id = ?";
        }
        
        $params = [$companyId];
        if ($sucursalId) {
            $params[] = $sucursalId;
        }
        $params[] = $productoId;

        return DB::select("SELECT
                    MAX(ad.id) as id,
                    ad.producto_linea_id,
                    COALESCE(ad.lote, CONCAT('LOTE-', MAX(ad.id))) as lote,
                    ad.fecha_vencimiento as fecha_vencimiento,
                    'ONIU' as empaque,
                    SUM(ad.cantidad) as unidades,
                    ad.pvp,
                    ad.pvc
                FROM
                    almacen_ingreso_detalle ad
                $joinIngresos
                WHERE ad.producto_id = ?
                GROUP BY ad.producto_linea_id, ad.lote, ad.fecha_vencimiento, ad.pvp, ad.pvc
                HAVING SUM(ad.cantidad) > 0
                ORDER BY MAX(ad.id) ASC", $params);
    }
}
