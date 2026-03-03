<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

$tables = [
    'familias' => 'familias_nombre_unique',
    'marcas' => 'marcas_nombre_unique',
    'unidades_medida' => 'unidades_medida_codigo_unique',
    'presentaciones' => 'presentaciones_nombre_unique',
    'concentraciones' => 'concentraciones_nombre_unique'
];

foreach ($tables as $tableName => $indexName) {
    try {
        echo "Processing $tableName ($indexName)...\n";
        Schema::table($tableName, function (Blueprint $table) use ($indexName, $tableName) {
            $column = ($tableName === 'unidades_medida') ? 'codigo' : 'nombre';
            try {
                $table->dropUnique($indexName);
                echo "  Dropped index $indexName\n";
            } catch (\Exception $e) {
                echo "  Could not drop index $indexName (already gone or different name)\n";
            }
            $table->unique([$column, 'company_id']);
        });
        echo "Success $tableName!\n";
    } catch (\Exception $e) {
        echo "Fail $tableName: " . $e->getMessage() . "\n";
    }
}

