<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CierreCaja;

echo "Open Sessions:\n";
foreach(CierreCaja::whereNull('fecha_cierre')->get() as $c) {
    echo "Caja: " . $c->caja->nombre . " - User: " . $c->user->name . " (ID: " . $c->user_id . ")\n";
}
