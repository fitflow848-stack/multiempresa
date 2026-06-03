<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Corrige el costo_unitario de ventas específicas afectadas por el bug
 * de FIFO multi-lote que usaba el costo del primer lote para todas las unidades.
 *
 * B001-107 (vd 7537): 2 unidades vendidas desde 2 lotes (costo 100 + costo 50)
 *   → costo promedio ponderado correcto = 75, ganancia = 70
 * B001-108 (vd 7538): 1 unidad desde lote costo 50
 *   → costo_unitario = 50 (correcto)
 */
return new class extends Migration
{
    public function up(): void
    {
        // B001-107: 2 lotes usados (costo 100 × 1 + costo 50 × 1) / 2 = 75
        DB::table('venta_detalles')->where('id', 7537)->update(['costo_unitario' => 75.0000]);

        // B001-108: 1 lote con costo 50
        DB::table('venta_detalles')->where('id', 7538)->update(['costo_unitario' => 50.0000]);
    }

    public function down(): void
    {
        DB::table('venta_detalles')->whereIn('id', [7537, 7538])->update(['costo_unitario' => 0]);
    }
};
