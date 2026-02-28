<?php

namespace App\Models;
use App\Traits\BelongsToCompany;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

use App\Traits\BelongsToSucursal;

class CierreCaja extends Model
{
    use HasFactory, BelongsToCompany, BelongsToSucursal;
    

    protected $companyForeignKey = 'id_empresa';
    protected $sucursalForeignKey = 'sucursal_id';
    
    protected $table = 'cierre_cajas';

    protected $fillable = [
        'user_id',
        'caja_id',
        'id_empresa',
        'sucursal_id',
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

    /**
     * Relación con el desglose del arqueo físico
     */
    public function arqueo()
    {
        return $this->hasOne(ArqueoCaja::class, 'cierre_id');
    }
}
