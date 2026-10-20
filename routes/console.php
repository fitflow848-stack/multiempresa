<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Illuminate\Foundation\Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;
use App\Jobs\SendSalesToSunatJob;

// Envía a SUNAT los comprobantes del día al cierre (23:00) y reintenta los que fallaron en la mañana (ver SunatManager::procesarPendientes)
Schedule::command('sunat:send-pending')->twiceDaily(8, 23)->timezone('America/Lima')->withoutOverlapping();
// Avisa por correo de los comprobantes que no llegaron a SUNAT o tienen error
Schedule::command('sunat:alertar-errores')->dailyAt('23:30')->timezone('America/Lima');
// Revisa en SUNAT si aceptó o rechazó los comunicados de baja enviados
Schedule::command('sunat:consultar-bajas')->hourly()->between('8:00', '20:00')->timezone('America/Lima')->withoutOverlapping();
