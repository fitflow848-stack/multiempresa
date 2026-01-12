<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CotizacionDetalle extends Model
{
    use HasFactory;

    protected $table = 'cotizacion_detalles';

    protected $fillable = [
        'cotizacion_id',
        'producto_id',
        'descripcion',
        'cantidad',
        'precio_unitario',
        'descuento',
        'subtotal',
        'lote',
        'fecha_vencimiento'
    ];

    protected $casts = [
        'cantidad' => 'decimal:3',
        'precio_unitario' => 'decimal:2',
        'descuento' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'fecha_vencimiento' => 'date'
    ];

    /**
     * Relación con la cotización
     */
    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class);
    }

    /**
     * Relación con el producto (opcional)
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    /**
     * Calcular el total de la línea (cantidad * precio - descuento)
     */
    public function getTotalAttribute()
    {
        return ($this->cantidad * $this->precio_unitario) - $this->descuento;
    }

    /**
     * Obtener precio con descuento aplicado
     */
    public function getPrecioConDescuentoAttribute()
    {
        $descuentoPorUnidad = $this->descuento / $this->cantidad;
        return $this->precio_unitario - $descuentoPorUnidad;
    }

    /**
     * Scope para obtener detalles de una cotización específica
     */
    public function scopeCotizacion($query, $cotizacionId)
    {
        return $query->where('cotizacion_id', $cotizacionId);
    }

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Recalcular subtotal al guardar
        static::saving(function ($detalle) {
            $detalle->subtotal = ($detalle->cantidad * $detalle->precio_unitario) - $detalle->descuento;
        });
    }
}