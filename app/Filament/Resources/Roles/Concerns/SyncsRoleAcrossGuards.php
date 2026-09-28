<?php

namespace App\Filament\Resources\Roles\Concerns;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * El formulario de Roles solo edita la copia "admin" de un rol (la que usa
 * el panel). Pero la app real (POS, sidebar, etc.) autentica con el guard
 * "web", y Spatie resuelve los permisos ahí contra la copia "web" del mismo
 * rol — que si no se mantiene sincronizada, deja los cambios del panel sin
 * ningún efecto para el usuario final. Este trait replica los mismos
 * permisos (por nombre) hacia el rol "web" homónimo de la misma empresa,
 * creándolo si todavía no existe.
 */
trait SyncsRoleAcrossGuards
{
    private function syncWebGuardRole(Role $adminRole, array $permissionNames): void
    {
        $webRole = Role::firstOrCreate([
            'name' => $adminRole->name,
            'guard_name' => 'web',
            'company_id' => $adminRole->company_id,
        ]);

        $webPermissionNames = Permission::whereIn('name', $permissionNames)
            ->where('guard_name', 'web')
            ->pluck('name')
            ->toArray();

        $webRole->syncPermissions($webPermissionNames);
    }
}
