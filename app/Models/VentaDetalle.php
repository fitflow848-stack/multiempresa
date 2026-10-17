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
        'orden',
        'almacen_ingreso_detalle_id',
        'costo_unitario'
    ];

    protected $casts = [
        'cantidad' => 'decimal:2',
        'precio_unitario' => 'decimal:2',
        'precio_total' => 'decimal:2',
        'igv' => 'decimal:2',
        'orden' => 'integer',
        'costo_unitario' => 'decimal:4'
    ];

    protected $appends = ['subtotal', 'igv_linea'];

    /**
     * Alias para importe (usado en plantillas PDF que comparten lógica con Cotizaciones)
     */
    public function getSubtotalAttribute()
    {
        return $this->importe;
    }

    /**
     * IGV de la línea para mostrarlo en pantalla (la tabla no guarda IGV por línea).
     * Producto exonerado/inafecto o venta sin IGV: 0. Gravado: el precio incluye IGV (importe - importe / 1.18).
     */
    public function getIgvLineaAttribute()
    {
        $venta = $this->venta;
        if (!$venta || (float) $venta->igv <= 0) {
            return 0;
        }

        $tipo = strtolower((string) ($this->producto->tipo_impuesto ?? 'gravado'));
        if (in_array($tipo, ['exonerado', 'inafecto', '20', '30'], true)) {
            return 0;
        }

        $importe = (float) $this->importe;
        return round($importe - ($importe / 1.18), 3);
    }

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

    public function almacenIngresoDetalle()
    {
        return $this->belongsTo(AlmacenIngresoDetalle::class, 'almacen_ingreso_detalle_id');
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
