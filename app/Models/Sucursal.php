<?php

namespace App\Models;
use App\Traits\BelongsToCompany;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sucursal extends Model
{
    use HasFactory, BelongsToCompany;
    

    protected $table = 'sucursales';

    protected $fillable = [
        'company_id',
        'nombre',
        'direccion',
        'telefono',
        'logo',
        'numero_cajas',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'numero_cajas' => 'integer',
    ];

    // ─── Relaciones ─────────────────────────────────────────────

    /**
     * Empresa a la que pertenece esta sucursal
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * Documentos de la sucursal
     */
    public function documents(): HasMany
    {
        return $this->hasMany(CompanyDocument::class, 'branch_id');
    }

    /**
     * Cajas de esta sucursal
     */
    public function cajas(): HasMany
    {
        return $this->hasMany(Caja::class);
    }

    /**
     * Usuarios asignados a esta sucursal
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'branch_id');
    }

    // ─── Accessors ──────────────────────────────────────────────

    /**
     * Obtener la URL completa del logo de la sucursal
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo) {
            // Fallback: usar el logo de la empresa
            return $this->company?->logo_url;
        }

        if (filter_var($this->logo, FILTER_VALIDATE_URL)) {
            return $this->logo;
        }

        return asset('storage/' . $this->logo);
    }

    /**
     * Obtener la ruta completa del archivo del logo para uso interno
     */
    public function getLogoPathAttribute(): ?string
    {
        if (!$this->logo) {
            return null;
        }

        return storage_path('app/public/' . $this->logo);
    }

    // ─── Scopes ─────────────────────────────────────────────────

    /**
     * Scope para sucursales activas
     */
    public function scopeActivas($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para sucursales de una empresa
     */
    public function scopeDeEmpresa($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}

