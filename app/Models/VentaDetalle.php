<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentaDetalle extends Model
{
    use HasFactory;

    protected $table = 'venta_detalles';

    protected $fillable = [
        'id_venta',
        'servicio_id',
        'nombre_servicio',
        'cantidad',
        'precio_unitario',
        'importe',
        'igv',
        'orden'
    ];

    protected $casts = [
        'cantidad' => 'decimal:2',
        'precio_unitario' => 'decimal:2',
        'precio_total' => 'decimal:2',
        'igv' => 'decimal:2',
        'orden' => 'integer'
    ];

    /**
     * Relación con la venta
     */
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'id_venta', 'id_venta');
    }

    /**
     * Relación con el producto
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'servicio_id');
    }

    // Scope para ordenar por item
    public function scopeOrdenado($query)
    {
        return $query->orderBy('orden', 'asc');
    }

    // Accessor para el precio total formateado
    public function getPrecioTotalFormateadoAttribute()
    {
        return number_format($this->precio_total, 2);
    }

    // Accessor para el precio unitario formateado
    public function getPrecioUnitarioFormateadoAttribute()
    {
        return number_format($this->precio_unitario, 2);
    }

    // Accessor para IGV formateado
    public function getIgvFormateadoAttribute()
    {
        return number_format($this->igv, 2);
    }
}