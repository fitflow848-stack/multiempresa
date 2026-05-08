<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BancoMovimiento extends Model
{
    use HasFactory;

    protected $fillable = [
        'cuenta_bancaria_id',
        'user_id',
        'tipo',
        'monto',
        'concepto',
        'referencia',
        'fecha',
        'cierre_caja_id',
        'id_venta',
    ];

    public function cuenta()
    {
        return $this->belongsTo(CuentaBancaria::class, 'cuenta_bancaria_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'id_venta');
    }
}
