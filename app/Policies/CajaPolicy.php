<?php

namespace App\Policies;

use App\Models\Caja;
use App\Models\User;

class CajaPolicy
{
    /**
     * Super admin tiene acceso total sin restricciones.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Puede ver cajas de su empresa.
     */
    public function viewAny(User $user): bool
    {
        // Usar check alternativo para evitar excepciones si el permiso no existe en el guard actual
        return $user->hasAnyPermission(['cajas.ver']); 
    }

    /**
     * Puede ver una caja específica de su empresa.
     */
    public function view(User $user, Caja $caja): bool
    {
        return $user->hasAnyPermission(['cajas.ver'])
            && $user->company_id === $caja->company_id;
    }

    /**
     * El dueño del negocio (admin_empresa) puede crear cajas en su empresa.
     * Es una función de administración del negocio, no de infraestructura.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['cajas.crear']);
    }

    /**
     * Puede editar cajas de su empresa.
     */
    public function update(User $user, Caja $caja): bool
    {
        return $user->hasAnyPermission(['cajas.editar'])
            && $user->company_id === $caja->company_id;
    }

    /**
     * Puede eliminar cajas de su empresa.
     */
    public function delete(User $user, Caja $caja): bool
    {
        return $user->hasAnyPermission(['cajas.eliminar'])
            && $user->company_id === $caja->company_id;
    }
}
