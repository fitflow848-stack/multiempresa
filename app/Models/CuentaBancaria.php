<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use App\Traits\BelongsToSucursal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CuentaBancaria extends Model
{
    use HasFactory, BelongsToCompany, BelongsToSucursal;

    protected $fillable = [
        'company_id',
        'sucursal_id',
        'banco_nombre',
        'tipo_cuenta',
        'numero_cuenta',
        'cci',
        'moneda',
        'saldo_inicial',
        'saldo_actual',
        'is_active',
    ];

    public function movimientos()
    {
        return $this->hasMany(BancoMovimiento::class);
    }
}
