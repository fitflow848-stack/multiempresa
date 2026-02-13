<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait BelongsToCompany
 *
 * Aplica un Global Scope para filtrar automáticamente los registros
 * por la empresa (company) del usuario autenticado.
 *
 * Requisito: El modelo debe tener una columna `id_empresa` o `company_id`.
 * Por defecto usa `id_empresa` (compatible con el proyecto actual).
 *
 * Uso:
 *   use App\Traits\BelongsToCompany;
 *   class Venta extends Model { use BelongsToCompany; }
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
            if (auth()->check() && !auth()->user()->hasRole('super_admin')) {
                $instance = new static;
                $column = $instance->getCompanyForeignKey();
                $query->where($query->getModel()->getTable() . '.' . $column, auth()->user()->company_id);
            }
        });

        // Auto-asignar empresa al crear un registro
        static::creating(function ($model) {
            if (auth()->check()) {
                $column = $model->getCompanyForeignKey();
                if (empty($model->{$column})) {
                    $model->{$column} = auth()->user()->company_id;
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
