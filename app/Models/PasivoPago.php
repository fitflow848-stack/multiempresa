<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasivoPago extends Model
{
    protected $fillable = [
        'pasivo_id',
        'user_id',
        'monto',
        'fecha_pago',
        'metodo_pago',
        'documento_pago',
        'observaciones'
    ];

    protected $casts = [
        'fecha_pago' => 'date',
        'monto' => 'decimal:2'
    ];

    public function pasivo()
    {
        return $this->belongsTo(Pasivo::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
