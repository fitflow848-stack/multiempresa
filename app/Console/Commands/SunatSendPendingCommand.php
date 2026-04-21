<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SunatSendPendingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sunat:send-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía las ventas pendientes a SUNAT';

    /**
     * Execute the console command.
     */
    public function handle(\App\Services\SunatManager $sunatManager)
    {
        $this->info('Iniciando envío de documentos pendientes a SUNAT...');
        
        $result = $sunatManager->procesarPendientes();
        
        $procesados = count($result['processed']);
        $fallidos = count($result['failed']);

        $this->info("Proceso finalizado.");
        $this->info("Procesados: {$procesados}");
        
        if ($fallidos > 0) {
            $this->error("Fallidos: {$fallidos}");
            foreach ($result['failed'] as $fail) {
                $this->line("- Venta ID {$fail['id']}: {$fail['error']}");
            }
        }
    }
}
