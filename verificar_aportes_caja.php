<?php

/**
 * Script verificación aportes-caja
 * Ejecutar: php verificar_aportes_caja.php
 */

require_once __DIR__ . '/vendor/autoload.php';

// Cargar la aplicación Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Aporte;
use App\Models\OperacionCaja;
use Illuminate\Support\Facades\DB;

echo "=== VERIFICACIÓN PROBLEMA APORTES-CAJA ===\n\n";

try {
    // 1. Aportes totales
    $totalAportes = Aporte::count();
    $montoTotalAportes = Aporte::sum('monto');
    
    echo "📊 ESTADÍSTICAS GENERALES:\n";
    echo "- Total aportes registrados: {$totalAportes}\n";
    echo "- Monto total aportes: S/ " . number_format($montoTotalAportes, 2) . "\n\n";
    
    // 2. Aportes sin movimiento de caja
    echo "🔍 VERIFICACIÓN PROBLEMA:\n";
    $aportesSinCaja = DB::select("
        SELECT a.*, 
               CASE 
                   WHEN oc.id IS NOT NULL THEN 'CON CAJA' 
                   ELSE 'SIN CAJA' 
               END as estado
        FROM aportes a
        LEFT JOIN operaciones_caja oc ON (
            oc.tipo = 'ingreso' 
            AND oc.importe = a.monto 
            AND oc.partida LIKE CONCAT('Aporte - ', a.nombre, '%')
        )
        WHERE a.fecha_registro >= '2026-04-01'
        ORDER BY a.fecha_registro DESC
        LIMIT 10
    ");
    
    echo "Últimos 10 aportes desde abril 2026:\n";
    foreach ($aportesSinCaja as $aporte) {
        $estado = $aporte->estado === 'CON CAJA' ? '✅' : '❌';
        $fecha = date('Y-m-d', strtotime($aporte->fecha_registro));
        $monto = number_format($aporte->monto, 2);
        echo "  {$estado} ID:{$aporte->id} | S/ {$monto} | {$fecha} | {$aporte->nombre}\n";
    }
    
    // 3. Contar problemáticos
    $countSinCaja = DB::selectOne("
        SELECT COUNT(*) as total
        FROM aportes a
        LEFT JOIN operaciones_caja oc ON (
            oc.tipo = 'ingreso' 
            AND oc.importe = a.monto 
            AND oc.partida LIKE CONCAT('Aporte - ', a.nombre, '%')
        )
        WHERE oc.id IS NULL AND a.fecha_registro >= '2026-04-01'
    ")->total;
    
    echo "\n📈 RESUMEN:\n";
    echo "- Aportes SIN movimiento de caja: {$countSinCaja}\n";
    
    if ($countSinCaja > 0) {
        echo "❌ PROBLEMA DETECTADO: Hay aportes que no tienen movimientos de caja\n";
        echo "\n🛠️ SOLUCIONES:\n";
        echo "1. php artisan aportes:corregir-caja --dry-run (ver qué se corregirá)\n";
        echo "2. php artisan aportes:corregir-caja (corregir)\n";
    } else {
        echo "✅ PERFECTO: Todos los aportes tienen movimientos de caja\n";
    }
    
    // 4. Verificar último aporte
    echo "\n🔍 ÚLTIMO APORTE REGISTRADO:\n";
    $ultimoAporte = Aporte::latest()->first();
    if ($ultimoAporte) {
        echo "- ID: {$ultimoAporte->id}\n";
        echo "- Monto: S/ " . number_format($ultimoAporte->monto, 2) . "\n";
        echo "- Fecha: " . $ultimoAporte->fecha_registro->format('Y-m-d H:i:s') . "\n";
        echo "- Nombre: {$ultimoAporte->nombre}\n";
        
        // Verificar si tiene operación de caja
        $operacion = OperacionCaja::where('tipo', 'ingreso')
            ->where('importe', $ultimoAporte->monto)
            ->where('partida', 'like', '%' . $ultimoAporte->nombre . '%')
            ->first();
            
        if ($operacion) {
            echo "- Estado: ✅ TIENE movimiento de caja\n";
        } else {
            echo "- Estado: ❌ NO TIENE movimiento de caja\n";
        }
    }

    echo "\n=== VERIFICACIÓN COMPLETADA ===\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}