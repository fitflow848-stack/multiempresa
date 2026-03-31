<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Traits\BelongsToCompany;
use App\Traits\BelongsToSucursal;

class Caja extends Model
{
    use HasFactory, BelongsToCompany, BelongsToSucursal;


    protected $table = 'cajas';

    protected $fillable = [
        'company_id',
        'sucursal_id',
        'nombre',
        'descripcion',
        'is_active',
        'is_boveda',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_boveda' => 'boolean',
    ];

    /**
     * Sucursal a la que pertenece esta caja
     */
    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    /**
     * Usuarios asignados a esta caja
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'caja_user')
            ->withTimestamps();
    }

    /**
     * Cierres de caja asociados
     */
    public function cierres()
    {
        return $this->hasMany(CierreCaja::class);
    }

    /**
     * Saldo actual: monto_apertura del último cierre de caja
     */
    public function getSaldo(): float
    {
        $ultimo = $this->cierres()->latest()->first();
        return $ultimo ? (float) $ultimo->monto_apertura : 0.0;
    }

    /**
     * Obtener la empresa a través de la sucursal
     */
    public function getCompanyAttribute()
    {
        return $this->sucursal?->company;
    }

    /**
     * Scope para cajas activas
     */
    public function scopeActivas($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para cajas de una sucursal específica
     */
    public function scopeDeSucursal($query, $sucursalId)
    {
        return $query->where('sucursal_id', $sucursalId);
    }
}
