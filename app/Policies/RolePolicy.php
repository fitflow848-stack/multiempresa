<?php

namespace App\Policies;

use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RolePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin_empresa');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Role $role): bool
    {
        return $user->hasRole('admin_empresa');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // El dueño del negocio puede crear roles personalizados para su empresa
        return $user->hasPermissionTo('roles.crear');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Role $role): bool
    {
        // No puede editar roles de sistema (super_admin, admin_empresa)
        $systemRoles = ['super_admin', 'admin_empresa'];
        if (in_array($role->name, $systemRoles)) {
            return false;
        }
        return $user->hasPermissionTo('roles.editar');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Role $role): bool
    {
        // No puede eliminar roles críticos del sistema
        $systemRoles = ['super_admin', 'admin_empresa', 'admin', 'administrador'];
        if (in_array($role->name, $systemRoles)) {
            return false;
        }
        return $user->hasPermissionTo('roles.eliminar');
    }
}
