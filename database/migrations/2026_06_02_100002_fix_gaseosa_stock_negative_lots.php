<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Los lotes con cantidad negativa y observacion NULL son imposibles en inventario real
 * y los ajustes manuales en almacen crean lotes separados [AJUSTE] que el display de
 * inventario excluye, dejando los lotes originales sin aplicar la reduccion.
 *
 * Para producto 39 (gaseosa cocacola) en sucursal 21:
 *   - Lotes negativos NULL-observacion: 43 (-2), 53 (-1), 1066 (-10)
 *   - Lote 2032 (+8): el ajuste -18 debia haberlo vaciado pero no lo logro por el filtro
 *   - Resultado actual en inventario: 8 - 2 - 1 - 10 + 5(recepcion) = 0
 *   - Resultado correcto: 0 + 5(recepcion) = 5
 */
return new class extends Migration
{
    private array $cambios = [
        // [lot_id, cantidad_original, cantidad_nueva]
        [43,   -2.00, 0.00],
        [53,   -1.00, 0.00],
        [1066, -10.00, 0.00],
        [2032,  8.00, 0.00],
    ];

    public function up(): void
    {
        foreach ($this->cambios as [$id, $original, $nueva]) {
            DB::table('almacen_ingreso_detalle')
                ->where('id', $id)
                ->where('cantidad', $original) // Seguridad: solo actualiza si el valor no cambió
                ->update(['cantidad' => $nueva]);
        }

        // Recalcular productos.cantidad para producto 39 desde la suma real de lotes
        $sumLotes = DB::table('almacen_ingreso_detalle')
            ->where('producto_id', 39)
            ->sum('cantidad');

        DB::table('productos')
            ->where('id', 39)
            ->update(['cantidad' => max(0, $sumLotes)]);
    }

    public function down(): void
    {
        foreach ($this->cambios as [$id, $original, $nueva]) {
            DB::table('almacen_ingreso_detalle')
                ->where('id', $id)
                ->where('cantidad', $nueva)
                ->update(['cantidad' => $original]);
        }

        // Restaurar suma original
        $sumLotes = DB::table('almacen_ingreso_detalle')
            ->where('producto_id', 39)
            ->sum('cantidad');

        DB::table('productos')
            ->where('id', 39)
            ->update(['cantidad' => $sumLotes]);
    }
};
