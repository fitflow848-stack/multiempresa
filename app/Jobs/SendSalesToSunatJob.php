<?php

namespace App\Jobs;

use App\Services\SunatManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSalesToSunatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(SunatManager $sunatManager): void
    {
        Log::info('Ejecutando SendSalesToSunatJob...');
        
        $result = $sunatManager->procesarPendientes();
        
        Log::info('SendSalesToSunatJob finalizado.', [
            'procesados' => count($result['processed']),
            'fallidos' => count($result['failed'])
        ]);
    }
}
