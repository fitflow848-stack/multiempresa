<?php

namespace App\Policies;

use App\Models\Tesoreria;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TesoreriaPolicy
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
        return $user->can('tesoreria.ver') || $user->hasRole('administrador');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Tesoreria $tesoreria): bool
    {
        $canView = $user->can('tesoreria.ver') || $user->hasRole('administrador');
        return $canView && $user->company_id === $tesoreria->company_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('tesoreria.crear') || $user->hasRole('administrador');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Tesoreria $tesoreria): bool
    {
        $canUpdate = $user->can('tesoreria.editar') || $user->hasRole('administrador');
        return $canUpdate && $user->company_id === $tesoreria->company_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Tesoreria $tesoreria): bool
    {
        $canDelete = $user->can('tesoreria.eliminar') || $user->hasRole('administrador');
        return $canDelete && $user->company_id === $tesoreria->company_id;
    }
}
