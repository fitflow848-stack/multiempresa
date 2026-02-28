<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToCompany;
use App\Traits\BelongsToSucursal;





class ArqueoCaja extends Model
{
    use HasFactory, BelongsToCompany, BelongsToSucursal;
    
    protected $table = 'arqueo_cajas';

    protected $fillable = [
        'company_id',
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
