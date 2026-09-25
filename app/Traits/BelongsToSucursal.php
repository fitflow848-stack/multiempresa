<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait BelongsToSucursal
 *
 * Aplica un Global Scope para filtrar automáticamente los registros
 * por la sucursal del usuario autenticado.
 *
 * Solo aplica a roles operativos (vendedor, cajero, supervisor).
 * Los roles super_admin y administrador ven todas las sucursales de su empresa.
 *
 * Requisito: El modelo debe tener una columna `sucursal_id` o `sucursal`.
 *
 * Uso:
 *   use App\Traits\BelongsToSucursal;
 *   class Venta extends Model { use BelongsToSucursal; }
 */
trait BelongsToSucursal
{
    /**
     * Nombre de la columna que referencia a la sucursal.
     * Sobreescribir en el modelo si usa otro nombre.
     */
    public function getSucursalForeignKey(): string
    {
        return property_exists($this, 'sucursalForeignKey')
            ? $this->sucursalForeignKey
            : 'sucursal_id';
    }

    protected static function bootBelongsToSucursal(): void
    {
        // Global Scope: filtrar por sucursal del usuario logueado
        static::addGlobalScope('sucursal', function (Builder $query) {
            $user = \App\Helpers\AuthHelper::resolveAuthenticatedUser();

            if (!$user) {
                logger()->info("Trait: No user found for scope");
                return;
            }

            // Solo el super_admin y administrador ven todas las sucursales sin restricciones automáticas
            if ($user->hasRole('super_admin') || (method_exists($user, 'isAdmin') && $user->isAdmin())) {
                logger()->info("Trait: User is admin, skipping branch scope");
                return;
            }

            // Roles operativos y administrador: filtrar por su sucursal asignada
            if ($user->branch_id) {
                $instance = new static;
                $column = $instance->getSucursalForeignKey();
                logger()->info("Trait: Applying scope to " . $query->getModel()->getTable() . " column $column value " . $user->branch_id);
                $query->where($query->getModel()->getTable() . '.' . $column, $user->branch_id);
            } else {
                logger()->info("Trait: User found but no branch_id");
            }
        });

        // Auto-asignar sucursal al crear
        static::creating(function ($model) {
            $user = \App\Helpers\AuthHelper::resolveAuthenticatedUser();
            if ($user) {
                $column = $model->getSucursalForeignKey();
                if (empty($model->{$column}) && $user->branch_id) {
                    $model->{$column} = $user->branch_id;
                }
            }
        });
    }

    /**
     * Scope para filtrar manualmente por sucursal
     */
    public function scopeDeSucursal(Builder $query, int $sucursalId): Builder
    {
        return $query->where($this->getSucursalForeignKey(), $sucursalId);
    }
}
