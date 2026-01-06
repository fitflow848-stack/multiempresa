<?php

namespace App\Console\Commands;

use App\Models\Venta;
use App\Models\VentaDetalle;
use Illuminate\Console\Command;

class CorregirIgvDuplicado extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ventas:corregir-igv {--dry-run : Solo mostrar qué se corregiría sin hacer cambios}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrige el IGV duplicado en las ventas existentes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('Modo DRY-RUN: Solo se mostrarán los cambios sin aplicarlos.');
        }

        // Buscar ventas con posible IGV duplicado
        $ventas = Venta::where('apli_igv', true)
                      ->where('igv', '>', 0)
                      ->with('detalles')
                      ->get();

        $ventasCorregidas = 0;
        $detallesCorregidos = 0;

        foreach ($ventas as $venta) {
            // Calcular el total actual sumando los detalles
            $totalDetalles = $venta->detalles->sum('precio_total');
            
            if ($totalDetalles == 0) {
                continue; // Saltar ventas sin detalles
            }

            // Si el total de la venta es mayor que el total de detalles,
            // probablemente hay IGV duplicado
            $diferenciaTotal = abs($venta->total - $totalDetalles);
            $igvCalculado = round($totalDetalles / 1.18, 2); // Base sin IGV
            $igvReal = round($totalDetalles - $igvCalculado, 2);

            // Verificar si hay discrepancia significativa (más de 0.10 soles)
            if ($diferenciaTotal > 0.10 || abs($venta->igv - $igvReal) > 0.10) {
                $this->info("Venta ID {$venta->id_venta}:");
                $this->info("  Total actual: S/ {$venta->total}");
                $this->info("  Total detalles: S/ {$totalDetalles}");
                $this->info("  IGV actual: S/ {$venta->igv}");
                $this->info("  IGV correcto: S/ {$igvReal}");
                $this->info("  Base gravada: S/ {$igvCalculado}");

                if (!$dryRun) {
                    // Corregir la venta
                    $venta->total = $totalDetalles;
                    $venta->igv = $igvReal;
                    $venta->save();

                    // Corregir los detalles para que tengan el IGV separado correctamente
                    foreach ($venta->detalles as $detalle) {
                        $precio_sin_igv = round($detalle->precio_unitario / 1.18, 4);
                        $precio_total = $detalle->precio_unitario * $detalle->cantidad;
                        $igv_detalle = round($precio_total - ($precio_sin_igv * $detalle->cantidad), 2);

                        $detalle->precio_total = $precio_total;
                        $detalle->igv = $igv_detalle;
                        $detalle->save();

                        $detallesCorregidos++;
                    }
                }

                $ventasCorregidas++;
                $this->info("  " . ($dryRun ? '[SIMULADO]' : '[CORREGIDO]'));
                $this->info('');
            }
        }

        if ($dryRun) {
            $this->info("Se encontraron {$ventasCorregidas} ventas que necesitan corrección.");
            $this->info("Ejecuta el comando sin --dry-run para aplicar los cambios.");
        } else {
            $this->info("Se corrigieron {$ventasCorregidas} ventas y {$detallesCorregidos} detalles.");
        }

        return 0;
    }
}