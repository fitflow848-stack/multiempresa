<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArqueoCaja extends Model
{
    protected $table = 'arqueo_cajas';

    protected $fillable = [
        'user_id',
        'cierre_id',
        'fecha',
        'monedas',
        'billetes',
        'total',
        'total_caja',
        'total_cierre',
        'descuento',
        'ultimo_conteo',
        'notas',
        'sucursal_id'
    ];

    protected $casts = [
        'monedas' => 'array',
        'billetes' => 'array',
        'fecha' => 'datetime',
        'ultimo_conteo' => 'datetime',
        'total' => 'float',
        'total_caja' => 'float',
        'total_cierre' => 'float',
        'descuento' => 'float'
    ];
}
