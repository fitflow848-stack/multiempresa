<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoPago extends Model
{
    use HasFactory;

    protected $table = 'tipos_pagos';

    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
        'icono',
        'color',
        'activo',
        'requiere_referencia',
        'es_digital',
        'es_efectivo',
        'comision_porcentaje',
        'comision_fija',
        'orden'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'requiere_referencia' => 'boolean',
        'es_digital' => 'boolean',
        'es_efectivo' => 'boolean',
    ];

    // Scope para obtener solo tipos de pago activos
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    // Scope para métodos digitales
    public function scopeDigitales($query)
    {
        return $query->where('es_digital', true);
    }

    // Scope para efectivo
    public function scopeEfectivo($query)
    {
        return $query->where('es_efectivo', true);
    }

    // Relación con ventas (cuando implementes el modelo Venta)
    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }
}