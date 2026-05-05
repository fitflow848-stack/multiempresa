<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\AlmacenIngresoDetalle;
use App\Models\Producto;

class ReconciliarStock extends Command
{
    protected $signature = 'stock:reconciliar
        {--ejecutar : Aplica los cambios (sin esta opción solo muestra el diagnóstico)}
        {--forzar  : Confirma aunque haya productos que no se puedan corregir al 100%}';

    protected $description = 'Reconcilia el stock del sistema con el saldo real del Kardex';

    public function handle()
    {
        $ejecutar = $this->option('ejecutar');
        $forzar   = $this->option('forzar');

        $this->info($ejecutar
            ? '=== RECONCILIACIÓN DE STOCK (MODO EJECUCIÓN) ==='
            : '=== DIAGNÓSTICO DE STOCK (solo lectura, no modifica nada) ===');
        $this->newLine();

        $discrepancias = DB::select("
            SELECT
                i.company_id,
                d.producto_id,
                d.producto_linea_id,
                i.sucursal_id,
                MAX(p.nombre) AS nombre,
                SUM(d.cantidad) AS stock_kardex,
                SUM(CASE WHEN (i.observacion IS NULL OR i.observacion NOT LIKE '[AJUSTE]%' OR d.cantidad >= 0)
                         THEN d.cantidad ELSE 0 END) AS stock_sistema
            FROM almacen_ingreso_detalle d
            INNER JOIN almacen_ingresos i ON i.id = d.ingreso_id
            INNER JOIN productos p ON p.id = d.producto_id
            GROUP BY i.company_id, d.producto_id, d.producto_linea_id, i.sucursal_id
            HAVING SUM(CASE WHEN (i.observacion IS NULL OR i.observacion NOT LIKE '[AJUSTE]%' OR d.cantidad >= 0)
                            THEN d.cantidad ELSE 0 END)
                 > SUM(d.cantidad) + 0.01
            ORDER BY (SUM(CASE WHEN (i.observacion IS NULL OR i.observacion NOT LIKE '[AJUSTE]%' OR d.cantidad >= 0)
                                THEN d.cantidad ELSE 0 END) - SUM(d.cantidad)) DESC
        ");

        if (empty($discrepancias)) {
            $this->info('No se encontraron discrepancias. El stock está correcto.');
            return 0;
        }

        $this->info('Productos con exceso de stock (sistema > kardex):');
        $this->table(
            ['Producto', 'Suc.', 'Sistema', 'Kardex', 'Exceso'],
            array_map(fn($r) => [
                substr($r->nombre, 0, 40),
                $r->sucursal_id,
                number_format($r->stock_sistema, 2),
                number_format($r->stock_kardex, 2),
                number_format($r->stock_sistema - $r->stock_kardex, 2),
            ], $discrepancias)
        );

        $totalProductos = count($discrepancias);
        $this->newLine();
        $this->info("Total: $totalProductos productos afectados.");

        if (!$ejecutar) {
            $this->newLine();
            $this->warn('Para aplicar la corrección ejecuta:');
            $this->line('  php artisan stock:reconciliar --ejecutar');
            $this->newLine();
            $this->line('Si algún producto no se puede corregir al 100% el comando cancela todo (seguro).');
            $this->line('Usa --forzar para guardar lo que se pueda aunque queden pendientes.');
            return 0;
        }

        if (!$this->confirm("¿Confirmar la corrección de $totalProductos productos?", false)) {
            $this->info('Cancelado.');
            return 0;
        }

        $this->newLine();
        $corregidos = 0;
        $fallidos   = [];

        DB::beginTransaction();
        try {
            foreach ($discrepancias as $disc) {
                $exceso = round($disc->stock_sistema - $disc->stock_kardex, 4);
                if ($exceso <= 0) continue;

                // FIFO: lotes [AJUSTE] primero (son phantom), luego regulares
                $lotesPositivos = AlmacenIngresoDetalle::join('almacen_ingresos as ai', 'ai.id', '=', 'almacen_ingreso_detalle.ingreso_id')
                    ->where('almacen_ingreso_detalle.producto_id', $disc->producto_id)
                    ->where('almacen_ingreso_detalle.producto_linea_id', $disc->producto_linea_id)
                    ->where('ai.sucursal_id', $disc->sucursal_id)
                    ->where('ai.company_id', $disc->company_id)
                    ->where('almacen_ingreso_detalle.cantidad', '>', 0)
                    ->orderByRaw("CASE WHEN ai.observacion LIKE '[AJUSTE]%' THEN 0 ELSE 1 END ASC")
                    ->orderBy('almacen_ingreso_detalle.id', 'asc')
                    ->select('almacen_ingreso_detalle.*')
                    ->lockForUpdate()
                    ->get();

                $pendiente = $exceso;
                foreach ($lotesPositivos as $lote) {
                    if ($pendiente <= 0) break;
                    $reducir = min((float) $lote->cantidad, $pendiente);
                    $lote->decrement('cantidad', $reducir);
                    $pendiente = round($pendiente - $reducir, 4);
                }

                $descontado = round($exceso - $pendiente, 4);

                if ($descontado > 0) {
                    // Sincronizar también el campo denormalizado productos.cantidad
                    Producto::where('id', $disc->producto_id)
                        ->decrement('cantidad', $descontado);
                }

                if ($pendiente > 0.01) {
                    $fallidos[] = sprintf('%-40s suc=%s  pendiente=%.4f', substr($disc->nombre, 0, 40), $disc->sucursal_id, $pendiente);
                } else {
                    $corregidos++;
                }
            }

            if (!empty($fallidos) && !$forzar) {
                DB::rollBack();
                $this->newLine();
                $this->error('CANCELADO — se revirtió todo. Los siguientes productos no tienen stock suficiente para absorber el exceso:');
                foreach ($fallidos as $f) {
                    $this->line("  · $f");
                }
                $this->newLine();
                $this->warn('Opciones:');
                $this->line('  1) Corre con --forzar para guardar lo que se pueda y ver qué queda pendiente.');
                $this->line('  2) Ajusta manualmente esos productos en Almacén → Ajustar Existencias.');
                return 1;
            }

            DB::commit();
            $this->newLine();
            $this->info("Corrección completada: $corregidos productos reconciliados.");

            if (!empty($fallidos)) {
                $this->warn(count($fallidos) . ' productos con corrección parcial (--forzar activo):');
                foreach ($fallidos as $f) {
                    $this->line("  · $f");
                }
            }

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Error inesperado — se revirtió todo: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
