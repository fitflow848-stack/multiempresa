<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Registros para ALIC-069 por sucursal/lote ===\n";
$result = DB::table('almacen_ingreso_detalle as d')
    ->join('almacen_ingresos as i', 'i.id', '=', 'd.ingreso_id')
    ->join('producto_lineas as pl', 'pl.id', '=', 'd.producto_linea_id')
    ->leftJoin('sucursales as s', 's.id', '=', 'i.sucursal_id')
    ->where('pl.cb', 'ALIC-069')
    ->select('d.id', 'i.sucursal_id', 's.nombre as sucursal', 'd.lote', 'd.fecha_vencimiento', 'd.cantidad')
    ->orderBy('i.sucursal_id')
    ->orderBy('d.lote')
    ->get();

foreach ($result as $row) {
    echo "ID={$row->id} | Sucursal={$row->sucursal_id}({$row->sucursal}) | Lote=" . ($row->lote ?: 'S/L') . " | Vence={$row->fecha_vencimiento} | Cantidad={$row->cantidad}\n";
}

echo "\n=== Totales agrupados ===\n";
$totals = DB::table('almacen_ingreso_detalle as d')
    ->join('almacen_ingresos as i', 'i.id', '=', 'd.ingreso_id')
    ->join('producto_lineas as pl', 'pl.id', '=', 'd.producto_linea_id')
    ->leftJoin('sucursales as s', 's.id', '=', 'i.sucursal_id')
    ->where('pl.cb', 'ALIC-069')
    ->select('i.sucursal_id', 's.nombre as sucursal', 'd.lote', 'd.fecha_vencimiento', DB::raw('SUM(d.cantidad) as total'))
    ->groupBy('i.sucursal_id', 's.nombre', 'd.lote', 'd.fecha_vencimiento')
    ->get();

foreach ($totals as $row) {
    echo "Sucursal={$row->sucursal_id}({$row->sucursal}) | Lote=" . ($row->lote ?: 'S/L') . " | Vence={$row->fecha_vencimiento} | Total={$row->total}\n";
}

echo "\n=== Columnas de venta_detalles ===\n";
$cols = DB::getSchemaBuilder()->getColumnListing('venta_detalles');
echo implode(', ', $cols) . "\n";

echo "\n=== Ventas usando lote ID=694 (buscar columa) ===\n";
$ventas = DB::table('venta_detalles')->where('almacen_ingreso_detalle_id', 694)->get();
foreach ($ventas as $v) {
    echo json_encode($v) . "\n";
}
if ($ventas->isEmpty()) echo "Ninguna\n";

echo "\n=== Transferencias con origen lote ID=694 ===\n";
$trfs = DB::table('almacen_transferencias')->where('origen_lote_id', 694)->get();
foreach ($trfs as $t) {
    echo json_encode($t) . "\n";
}
if ($trfs->isEmpty()) echo "Ninguna\n";

echo "\n=== productos.cantidad para ALIC-069 ===\n";
$prod = DB::table('producto_lineas as pl')
    ->join('productos as p', 'p.id', '=', 'pl.producto_id')
    ->where('pl.cb', 'ALIC-069')
    ->select('p.id', 'p.nombre', 'p.cantidad', 'pl.id as linea_id', 'pl.cb', 'pl.presentacion')
    ->first();
echo "Producto ID={$prod->id} | Nombre={$prod->nombre} | cantidad(global)={$prod->cantidad} | LineaID={$prod->linea_id} | CB={$prod->cb}\n";
