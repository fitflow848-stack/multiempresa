<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait BelongsToCompany
 *
 * Aplica un Global Scope para filtrar automáticamente los registros
 * por la empresa (company) del usuario autenticado.
 *
 * Requisito: El modelo debe tener una columna `company_id`.
 */
trait BelongsToCompany
{
    /**
     * Nombre de la columna que referencia a la empresa.
     * Sobreescribir en el modelo si usa otro nombre.
     */
    public function getCompanyForeignKey(): string
    {
        return property_exists($this, 'companyForeignKey')
            ? $this->companyForeignKey
            : 'company_id';
    }

    /**
     * Resuelve el usuario autenticado sin importar el guard activo.
     * Filament usa el guard 'admin', el resto de la app usa 'web'.
     */
    protected static function resolveAuthUser(): ?\App\Models\User
    {
        // Intentar con el guard de Filament primero
        if (auth()->guard('admin')->check()) {
            return auth()->guard('admin')->user();
        }
        // Luego el guard web estándar
        if (auth()->guard('web')->check()) {
            return auth()->guard('web')->user();
        }
        return null;
    }

    protected static function bootBelongsToCompany(): void
    {
        // Global Scope: filtrar por empresa del usuario logueado
        static::addGlobalScope('company', function (Builder $query) {
            $user = static::resolveAuthUser();

            if ($user && !$user->hasRole('super_admin')) {
                $instance = new static;
                $column = $instance->getCompanyForeignKey();
                $query->where($query->getModel()->getTable() . '.' . $column, $user->company_id);
            }
        });

        // Auto-asignar empresa al crear un registro
        static::creating(function ($model) {
            $user = static::resolveAuthUser();

            if ($user) {
                $column = $model->getCompanyForeignKey();
                if (empty($model->{$column})) {
                    $model->{$column} = $user->company_id;
                }
            }
        });
    }

    /**
     * Scope para filtrar manualmente por empresa (útil para super_admin)
     */
    public function scopeDeEmpresa(Builder $query, int $companyId): Builder
    {
        return $query->where($this->getCompanyForeignKey(), $companyId);
    }
}
