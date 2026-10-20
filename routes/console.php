<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Illuminate\Foundation\Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;
use App\Jobs\SendSalesToSunatJob;

// Envía a SUNAT los comprobantes que cumplieron 2 días calendario desde su emisión (el límite de SUNAT es 3) (ver SunatManager::procesarPendientes)
Schedule::command('sunat:send-pending')->dailyAt('11:00')->timezone('America/Lima')->withoutOverlapping();
// Revisa en SUNAT si aceptó o rechazó los comunicados de baja enviados
Schedule::command('sunat:consultar-bajas')->hourly()->between('8:00', '20:00')->timezone('America/Lima')->withoutOverlapping();
