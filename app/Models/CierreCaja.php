<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class CierreCaja extends Model
{
    use HasFactory;

    protected $table = 'cierre_cajas';

    protected $fillable = [
        'user_id',
        'caja_id',
        'id_empresa',
        'fecha_cierre',
        'monto_apertura', // Saldo Inicial
        'monto_cierre',   // Cierre Caja (Efectivo Real)
        'ingresos',
        'egresos',        // Gastos
        'aportaciones',   // Nuevo: Dinero extra ingresado
        'sustracciones',  // Nuevo: Retiros de caja
        'observaciones',
    ];

    protected $casts = [
        'fecha_cierre' => 'datetime',
    ];

    /**
     * Relación con el usuario que realiza el cierre
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con la caja
     */
    public function caja()
    {
        return $this->belongsTo(Caja::class);
    }
}
