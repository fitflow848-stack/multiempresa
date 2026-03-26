<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    /**
     * Super admin tiene acceso total: crea empresas, las edita, las elimina.
     * Esta es su función exclusiva en el sistema.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * El admin_empresa puede ver su propia empresa en el panel para conocer los datos.
     * El super_admin ve todas las empresas (gestionado por before()).
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdminEmpresa() || $user->hasPermissionTo('empresas.ver');
    }

    /**
     * Puede ver el detalle de su propia empresa.
     */
    public function view(User $user, Company $company): bool
    {
        if ($user->isSuperAdmin()) return true;

        return ($user->isAdminEmpresa() || $user->hasPermissionTo('empresas.ver'))
            && $user->company_id === $company->id;
    }

    /**
     * SOLO el super_admin puede crear empresas nuevas.
     * El admin_empresa no tiene esta función.
     */
    public function create(User $user): bool
    {
        return false; // Solo super_admin via before()
    }

    /**
     * El admin_empresa puede actualizar los datos de su propia empresa
     * (razón social, dirección, logo, configuración fiscal, etc.).
     * No puede modificar empresas ajenas.
     */
    public function update(User $user, Company $company): bool
    {
        if ($user->isSuperAdmin()) return true;

        return ($user->isAdminEmpresa() || $user->hasPermissionTo('empresas.editar'))
            && $user->company_id === $company->id;
    }

    /**
     * SOLO el super_admin puede eliminar empresas.
     */
    public function delete(User $user, Company $company): bool
    {
        return false; // Solo super_admin via before()
    }
}
