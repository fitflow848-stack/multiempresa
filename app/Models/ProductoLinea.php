<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoLinea extends Model
{
    use HasFactory;

    protected $table = 'producto_lineas';

    protected $fillable = [
        'producto_id',
        'cb',
        'codigo_ref',
        'presentacion',
        'concentracion',
        'cantidad',
        'precio_compra',
        'pvp',
        'pvp_dto',
        'peso',
        'pa1',
        'pa2',
        'lote',
        'fecha_venc',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_compra' => 'decimal:2',
        'pvp' => 'decimal:2',
        'pvp_dto' => 'decimal:2',
        'peso' => 'decimal:3',
        'fecha_venc' => 'date',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}