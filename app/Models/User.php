<?php

namespace App\Models;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Traits\HasRoles;

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

    public function getCompanyIdAttribute($value)
    {
        // Solo aplicar lógica de sesión para el usuario que está navegando
        $authenticatedUserId = auth()->id();
        
        if ($authenticatedUserId === $this->id) {
            // Nota: Evitamos hasRole() aquí para no causar recursión infinita
            // ya que hasRole() podría llamar a company_id si Spatie está configurado para teams
            $isSuperAdmin = \Illuminate\Support\Facades\DB::table('model_has_roles')
                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->where('model_has_roles.model_id', $this->id)
                ->where('roles.name', 'super_admin')
                ->exists();

            if ($isSuperAdmin) {
                return session('active_company_id', $value);
            }
        }
        return $value;
    }

    /**
     * Accessor para obtener la sucursal activa desde la sesión si es el usuario autenticado.
     */
    public function getBranchIdAttribute($value)
    {
        // Solo sobreescribir para el usuario autenticado en su propia sesión
        if (auth()->check() && auth()->id() === $this->id) {
            return session('active_branch_id', $value);
        }
        return $value;
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
     * Sucursal activa del usuario (la elegida para la sesión actual)
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'branch_id');
    }

    /**
     * Sucursales asignadas al usuario (muchos a muchos)
     */
    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Sucursal::class, 'branch_user', 'user_id', 'sucursal_id')
            ->withTimestamps();
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
        // Bypasseamos Spatie para esta comprobación crítica para evitar problemas de scoping al inicio
        return \Illuminate\Support\Facades\DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_id', $this->id)
            ->where('roles.name', 'super_admin')
            ->exists();
    }

    public function isAdmin(): bool
    {
        return \Illuminate\Support\Facades\DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_id', $this->id)
            ->whereIn('roles.name', ['super_admin', 'admin_empresa'])
            ->exists();
    }

    /**
     * ¿Es administrador de una empresa (dueño del negocio)?
     * Este rol gestiona usuarios, roles, cajas y configuración de su empresa.
     */
    public function isAdminEmpresa(): bool
    {
        // Usar DB directa para evitar problemas de scoping durante carga inicial/login
        return \Illuminate\Support\Facades\DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_id', $this->id)
            ->whereIn('roles.name', ['admin_empresa', 'admin', 'administrador', 'super_admin'])
            ->exists();
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
        // El scope se aplica automáticamente desde BelongsToCompany si el usuario tiene un company_id (AuthHelper lo inyecta), incluso para super_admin.

        // Si es admin o supervisor, ve todas las cajas de su sucursal actual (filtradas por el trait)
        if ($this->isSuperAdmin() || $this->isAdminEmpresa() || $this->hasRole('supervisor')) {
            $query = Caja::activas();

            // Forzamos el filtro por la sucursal actual para que roles altos como super_admin o admin_empresa 
            // solo vean en el dropdown las cajas de la sucursal desde la que han iniciado sesión
            if ($this->branch_id) {
                $query->where('sucursal_id', $this->branch_id);
            }

            return $query->get();
        }

        // Vendedor / cajero → solo las asignadas vía pivot de su sucursal activa
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
            // El admin_empresa puede acceder a cualquier caja de su empresa
            // (no se restringe por sucursal, él puede moverse entre ellas)
            return Caja::withoutGlobalScopes()
                ->where('id', $cajaId)
                ->where('company_id', $this->company_id)
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
        // Asegurar que el team id esté configurado para la verificación de roles
        if (config('permission.teams') && $this->company_id) {
            setPermissionsTeamId($this->company_id);
        }
        
        return $this->hasAnyRole(['super_admin', 'admin', 'admin_empresa', 'supervisor']);
    }
}
