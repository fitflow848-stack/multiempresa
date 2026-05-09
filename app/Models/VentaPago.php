<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentaPago extends Model
{
    use HasFactory;

    protected $fillable = [
        'venta_id',
        'tipo_pago_id',
        'monto',
        'referencia',
        'cuenta_bancaria_id',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id', 'id_venta');
    }

    public function tipoPago()
    {
        return $this->belongsTo(TipoPago::class, 'tipo_pago_id');
    }

    public function cuentaBancaria()
    {
        return $this->belongsTo(CuentaBancaria::class);
    }
}
