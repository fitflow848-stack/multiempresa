<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlmacenIngresoDetalle extends Model
{
    protected $table = 'almacen_ingreso_detalle';

    protected $fillable = [
        'ingreso_id',
        'producto_id',
        'producto_linea_id',
        'cantidad',
        'costo',
        'cop',
        'mu',
        'mud',
        'mup',
        'pvp',
        'pvpd',
        'pvc',
        'pvcd',
        'stock_min',
        'stock_max',
        'lote',
        'fecha_vencimiento'
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
        'cantidad' => 'float',
        'costo' => 'float',
        'pvp' => 'float',
    ];

    public function ingreso()
    {
        return $this->belongsTo(AlmacenIngreso::class, 'ingreso_id');
    }

    public function producto()
    {
        return $this->belongsTo(\App\Models\Producto::class, 'producto_id');
    }
}
