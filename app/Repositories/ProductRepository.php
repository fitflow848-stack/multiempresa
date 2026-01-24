<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class ProductRepository
{
    /**
     * Buscar productos (reemplaza el DB::select del controller).
     */
    public function buscar(string $q): array
    {
        return DB::select("
            SELECT
                p.id AS producto_id,
                ad.producto_linea_id AS product_linea_id,
                CONCAT_WS(' / ', p.nombre, CONCAT(pl.presentacion, ' ', pl.concentracion)) AS nombre,
                CONCAT(
                    'lt. ', ad.lote, ' Fv. ', LPAD(DAY(ad.fecha_vencimiento), 2, '0'),
                    ' ', LOWER(LEFT(MONTHNAME(ad.fecha_vencimiento), 3)), ' ', RIGHT(YEAR(ad.fecha_vencimiento), 2)
                ) AS detalle,
                SUM(ad.cantidad) AS cantidad_total,
                MAX(ad.pvp) AS pvp,
                MAX(ad.pvc) AS pvc,
                COUNT(ad.id) AS total_lotes,
                ad.fecha_vencimiento
            FROM almacen_ingreso_detalle ad
            INNER JOIN productos p ON p.id = ad.producto_id
            INNER JOIN producto_lineas pl ON pl.id = ad.producto_linea_id 
            WHERE (p.nombre LIKE ? OR p.codigo_barras LIKE ?) AND ad.cantidad > 0
            GROUP BY p.id, ad.producto_linea_id, p.nombre, pl.presentacion, pl.concentracion, ad.lote, ad.fecha_vencimiento
            ORDER BY p.nombre ASC
        ", ["%{$q}%", "%{$q}%"]);
    }

    public function obtenerLotes(int $productoId): array
    {
        return DB::select("SELECT
                    ad.id,
                    CONCAT('LOTE-', ad.id) as lote,
                    NULL as fecha_vencimiento,
                    ad.cantidad,
                    ad.pvp,
                    ad.pvc,
                    CONCAT('Stock: ', ad.cantidad) as descripcion_lote
                FROM almacen_ingreso_detalle ad
                WHERE ad.producto_id = ? AND ad.cantidad > 0
                ORDER BY ad.id ASC", [$productoId]);
    }

    public function obtenerProductoConLotes(int $productoId)
    {
        return DB::selectOne("SELECT p.* FROM productos p WHERE p.id = ?", [$productoId]);
    }


    public function elegirStock(int $productoId): array
    {
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
                WHERE ad.producto_id = ? AND ad.cantidad > 0
                ORDER BY ad.id ASC", [$productoId]);
    }
}