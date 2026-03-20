<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockService
{
    public function decrementarStock(int $almacenDetalleId, $cantidad): bool
    {
        if ($cantidad <= 0) return false;

        $updated = DB::table('almacen_ingreso_detalle')
            ->where('id', $almacenDetalleId)
            ->decrement('cantidad', $cantidad);

        if ($updated === 0) {
            Log::error("No se pudo actualizar stock. ID $almacenDetalleId no encontrado.");
            return false;
        }
        return true;
    }
}