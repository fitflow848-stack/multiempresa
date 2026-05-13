<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CuentaBancaria extends Model
{
    use HasFactory, BelongsToCompany;

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

    public function sucursal()
    {
        return $this->belongsTo(\App\Models\Sucursal::class);
    }

    /**
     * Obtener la cuenta bancaria preferida para el usuario actual.
     * Prioriza: cuenta activa de su sucursal > cualquier cuenta activa de la empresa.
     */
    public static function preferidaParaUsuario($user = null)
    {
        $user = $user ?? auth()->user();

        // Primero buscar cuenta activa en la sucursal del usuario
        $cuenta = static::where('company_id', $user->company_id)
            ->where('is_active', true)
            ->where('sucursal_id', $user->branch_id)
            ->orderBy('id')
            ->first();

        // Si no hay en su sucursal, tomar cualquier cuenta activa de la empresa
        if (!$cuenta) {
            $cuenta = static::where('company_id', $user->company_id)
                ->where('is_active', true)
                ->orderBy('id')
                ->first();
        }

        return $cuenta;
    }
}
