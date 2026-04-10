<?php

namespace App\Policies;

use App\Models\Sucursal;
use App\Models\User;

class SucursalPolicy
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
     * Puede ver la lista de sucursales de su empresa.
     * El admin_empresa necesita verlas para asignar usuarios y cajas.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('sucursales.ver');
    }

    /**
     * Puede ver el detalle de una sucursal de su empresa.
     */
    public function view(User $user, Sucursal $sucursal): bool
    {
        return $user->hasPermissionTo('sucursales.ver')
            && $user->company_id === $sucursal->company_id;
    }

    /**
     * Solo el super_admin puede crear sucursales (gestionadas por before()).
     * El admin_empresa NO puede: la estructura de sucursales la define el super_admin.
     */
    public function create(User $user): bool
    {
        return false; // Solo super_admin via before()
    }

    /**
     * Solo el super_admin puede editar sucursales (gestionadas por before()).
     * Excepto admin_empresa para sucursales de su propia empresa.
     */
    public function update(User $user, Sucursal $sucursal): bool
    {
        return $user->isAdminEmpresa()
            && (int)$user->attributes['company_id'] === (int)$sucursal->company_id;
    }

    /**
     * Solo el super_admin puede eliminar sucursales (gestionadas by before()).
     */
    public function delete(User $user, Sucursal $sucursal): bool
    {
        return false; // Solo super_admin via before()
    }
}
