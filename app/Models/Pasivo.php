<?php

namespace App\Models;
use App\Traits\BelongsToCompany;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToSucursal;

class Pasivo extends Model
{
    use HasFactory, BelongsToCompany, BelongsToSucursal;
    
    protected $fillable = [
        'company_id',
        'sucursal_id',
        'tipo_pasivo_id',
        'nombre',
        'empresa_persona',
        'monto',
        'monto_pagado',
        'estado',
        'fecha_registro',
        'documento',
        'observaciones',
        'user_id',
        'cierre_caja_id',
        'id_operacion_caja',
        'is_settled',
        'tipo_adelanto',
        'is_compra_credito'
    ];

    protected $casts = [
        'fecha_registro' => 'date',
        'monto' => 'decimal:2',
        'monto_pagado' => 'decimal:2'
    ];

    public function tipo()
    {
        return $this->belongsTo(TipoPasivo::class, 'tipo_pasivo_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cierreCaja()
    {
        return $this->belongsTo(CierreCaja::class);
    }

    public function operacionCaja()
    {
        return $this->belongsTo(OperacionCaja::class, 'id_operacion_caja');
    }

    public function pagos()
    {
        return $this->hasMany(PasivoPago::class);
    }

    public function getSaldoAttribute()
    {
        return $this->monto - $this->monto_pagado;
    }
}
