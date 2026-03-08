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

    protected static function bootBelongsToCompany(): void
    {
        // Global Scope: filtrar por empresa del usuario logueado
        static::addGlobalScope('company', function (Builder $query) {
            $user = \App\Helpers\AuthHelper::resolveAuthenticatedUser();

            // SÍ aplicamos el scope si el usuario tiene empresa (excepto para super_admin que debe ver todo)
            if ($user && $user->company_id && !$user->isSuperAdmin()) {
                $instance = new static;
                $column = $instance->getCompanyForeignKey();
                $query->where($query->getModel()->getTable() . '.' . $column, $user->company_id);
            }
        });

        // Auto-asignar empresa al crear un registro
        static::creating(function ($model) {
            $user = \App\Helpers\AuthHelper::resolveAuthenticatedUser();

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
