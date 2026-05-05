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

        // Nunca dejar el lote en negativo — el llamador debe distribuir entre lotes si necesita más
        $disponible = max(0.0, (float) $detalle->cantidad);
        $toDecrement = min((float) $cantidad, $disponible);

        if ($toDecrement <= 0) {
            Log::warning("Lote $almacenDetalleId sin stock disponible para descontar $cantidad unidades.");
            return false;
        }

        $detalle->decrement('cantidad', $toDecrement);

        if ($detalle->producto) {
            $detalle->producto->decrement('cantidad', $toDecrement);
        }

        return true;
    }
}