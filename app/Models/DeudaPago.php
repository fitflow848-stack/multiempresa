<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeudaPago extends Model
{
    use HasFactory;

    protected $table = 'deuda_pagos';

    protected $fillable = [
        'deuda_id',
        'user_id',
        'monto',
        'fecha_pago',
        'metodo_pago',
        'referencia',
        'codigo_comprobante',
        'observaciones'
    ];

    protected $casts = [
        'fecha_pago' => 'datetime',
        'monto' => 'decimal:2',
    ];

    public function deuda()
    {
        return $this->belongsTo(Deuda::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
