<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Traits\BelongsToCompany;

use Illuminate\Database\Eloquent\Model;

class Pasivo extends Model
{
    use HasFactory, BelongsToCompany;
    protected $fillable = [
        'company_id',
        'tipo_pasivo_id',
        'nombre',
        'empresa_persona',
        'monto',
        'monto_pagado',
        'estado',
        'fecha_registro',
        'documento',
        'observaciones'
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

    public function pagos()
    {
        return $this->hasMany(PasivoPago::class);
    }

    public function getSaldoAttribute()
    {
        return $this->monto - $this->monto_pagado;
    }
}
