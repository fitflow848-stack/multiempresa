<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_venta';

    protected $table = 'ventas';

    protected $fillable = [
        'id_tido',
        'tipo_documento',
        'id_tipo_pago',
        'condiciones_pago',
        'fecha_emision',
        'fecha_vencimiento',
        'dias_pagos',
        'direccion',
        'serie',
        'numero',
        'id_cliente',
        'total',
        'descuento_porcentaje',
        'descuento_monto',
        'aplica_detraccion',
        'detraccion_porcentaje',
        'detraccion_monto',
        'total_neto_pendiente',
        'total_cuotas',
        'cuotas',
        'estado',
        'enviado_sunat',
        'id_empresa',
        'sucursal',
        'apli_igv',
        'observacion',
        'igv',
        'medoto_pago_id',
        'pagado',
        'moneda',
        'cm_tc',
        'id_coti',
        'cierre_caja_id',
        'id_usuario',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'fecha_emision' => 'datetime',
        'fecha_vencimiento' => 'datetime',
        'total' => 'decimal:2',
        'igv' => 'decimal:2',
        'descuento_monto' => 'decimal:2',
        'detraccion_monto' => 'decimal:2',
        'total_neto_pendiente' => 'decimal:2',
        'total_cuotas' => 'decimal:2',
        'aplica_detraccion' => 'boolean',
        'apli_igv' => 'boolean',
        'enviado_sunat' => 'boolean',
        'pagado' => 'boolean',
    ];

    /**
     * Relación con los detalles de la venta
     */
    public function detalles()
    {
        return $this->hasMany(VentaDetalle::class, 'id_venta', 'id_venta')->ordenado();
    }

    /**
     * Relación con el cliente
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    /**
     * Relación con la empresa/compañía
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'id_empresa');
    }

    /**
     * Relación con el tipo de pago
     */
    public function tipoPago()
    {
        return $this->belongsTo(TipoPago::class, 'medoto_pago_id');
    }

    /**
     * Relación con SUNAT
     */
    public function ventaSunat()
    {
        return $this->hasOne(VentaSunat::class, 'id_venta', 'id_venta');
    }

    /**
     * Scope para ventas de una empresa específica
     */
    public function scopeEmpresa($query, $empresaId)
    {
        return $query->where('id_empresa', $empresaId);
    }

    /**
     * Scope para ventas por estado
     */
    public function scopeEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    /**
     * Scope para ventas pagadas
     */
    public function scopePagadas($query, $pagado = true)
    {
        return $query->where('pagado', $pagado);
    }

    /**
     * Accessor para obtener el tipo de documento
     */
    public function getTipoDocumentoAttribute($value)
    {
        // Si ya tiene valor en tipo_documento, usarlo
        if ($value) {
            return $value;
        }
        
        // Si no, intentar deducir del id_tido o serie
        if ($this->serie) {
            $serie = strtoupper($this->serie);
            if (str_starts_with($serie, 'F')) {
                return 'factura';
            } elseif (str_starts_with($serie, 'B')) {
                return 'boleta';
            } elseif (str_starts_with($serie, 'NV')) {
                return 'nota-venta';
            }
        }
        
        // Por defecto, ticket
        return 'ticket';
    }
}
