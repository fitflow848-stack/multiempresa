<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\BelongsToCompany;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'company_id',
        'branch_id',
        'is_active',
        'phone',
        'birth_date',
        'gender',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    // ─── Relaciones ─────────────────────────────────────────────

    /**
     * Empresa a la que pertenece el usuario
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Sucursal asignada al usuario
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'branch_id');
    }

    /**
     * Cajas asignadas al usuario (muchos a muchos)
     */
    public function cajas(): BelongsToMany
    {
        return $this->belongsToMany(Caja::class, 'caja_user')
            ->withTimestamps();
    }

    // ─── Helpers Multi-Tenant ───────────────────────────────────

    /**
     * ¿Es el administrador general del sistema?
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    /**
     * ¿Es administrador de una empresa?
     */
    public function isAdminEmpresa(): bool
    {
        return $this->hasRole('admin_empresa');
    }

    /**
     * Obtener las cajas a las que tiene acceso según su rol.
     *
     * - super_admin     → todas las cajas del sistema
     * - admin_empresa   → todas las cajas de su empresa
     * - supervisor      → todas las cajas de su sucursal
     * - vendedor/cajero  → solo las cajas asignadas via pivot
     */
    public function cajasDisponibles()
    {
        if ($this->isSuperAdmin()) {
            return Caja::activas()->get();
        }

        // Si el usuario tiene una sucursal asignada, filtramos por esa sucursal
        // independientemente de si es admin_empresa o supervisor.
        if ($this->branch_id) {
            return Caja::activas()
                ->where('sucursal_id', $this->branch_id)
                ->get();
        }

        if ($this->isAdminEmpresa()) {
            return Caja::activas()->get();
        }

        // vendedor / cajero → solo las asignadas
        return $this->cajas()->where('is_active', true)->get();
    }

    /**
     * Verificar si el usuario tiene acceso a una caja específica
     */
    public function tieneAccesoACaja(int $cajaId): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->isAdminEmpresa()) {
            return Caja::where('id', $cajaId)
                ->whereHas('sucursal', fn($q) => $q->where('company_id', $this->company_id))
                ->exists();
        }

        if ($this->hasRole('supervisor')) {
            return Caja::where('id', $cajaId)
                ->where('sucursal_id', $this->branch_id)
                ->exists();
        }

        return $this->cajas()->where('cajas.id', $cajaId)->exists();
    }

    // ─── Filament ───────────────────────────────────────────────

    /**
     * Determina si el usuario puede acceder al panel de Filament.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole(['super_admin', 'admin', 'admin_empresa', 'supervisor']);
    }
}
