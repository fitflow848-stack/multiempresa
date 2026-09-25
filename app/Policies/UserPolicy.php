<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
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
        return $user->can('usuarios.ver') || $user->hasRole('administrador');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        $canView = $user->can('usuarios.ver') || $user->hasRole('administrador');
        return $canView && $user->company_id === $model->company_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('usuarios.crear') || $user->hasRole('administrador');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        $canUpdate = $user->can('usuarios.editar') || $user->hasRole('administrador');
        return $canUpdate && $user->company_id === $model->company_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        $canDelete = $user->can('usuarios.eliminar') || $user->hasRole('administrador');
        return $canDelete && $user->company_id === $model->company_id && $user->id !== $model->id;
    }
}
