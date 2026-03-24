<?php
namespace App\Services;

use App\Models\AlmacenIngresoDetalle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockService
{
    public function decrementarStock(int $almacenDetalleId, $cantidad): bool
    {
        if ($cantidad <= 0) return false;

        $detalle = AlmacenIngresoDetalle::find($almacenDetalleId);
        if (!$detalle) {
            Log::error("No se pudo actualizar stock. ID $almacenDetalleId no encontrado.");
            return false;
        }

        // 1. Decrementar en el lote específico
        $detalle->decrement('cantidad', $cantidad);

        // 2. Decrementar el total en la tabla productos (Sync) 
        if ($detalle->producto) {
            $detalle->producto->decrement('cantidad', $cantidad);
        }

        return true;
    }
}