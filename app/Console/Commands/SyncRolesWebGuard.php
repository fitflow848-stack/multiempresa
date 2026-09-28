<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * El formulario de Roles del panel solo edita la copia "admin" de cada rol.
 * La app real (POS, sidebar, etc.) autentica con el guard "web" y resuelve
 * los permisos contra la copia "web" del mismo rol, que hasta ahora nunca se
 * actualizaba junto con la de "admin" (ver EditRole.php/CreateRole.php).
 * Este comando reconcilia los roles ya desincronizados: por cada rol
 * "admin", replica exactamente los mismos permisos (por nombre) hacia su
 * gemelo "web" de la misma empresa, creándolo si no existe.
 */
class SyncRolesWebGuard extends Command
{
    protected $signature = 'roles:sync-web-guard
        {--ejecutar : Aplica los cambios (sin esta opción solo muestra el diagnóstico)}';

    protected $description = 'Sincroniza los permisos del rol "web" con los de su gemelo "admin" para cada empresa';

    public function handle(): int
    {
        $ejecutar = (bool) $this->option('ejecutar');

        $this->info($ejecutar
            ? '=== SINCRONIZACIÓN ROLES admin -> web (MODO EJECUCIÓN) ==='
            : '=== DIAGNÓSTICO DE SINCRONIZACIÓN (solo lectura) ===');
        $this->newLine();

        $adminRoles = Role::where('guard_name', 'admin')->get();
        $corregidos = 0;

        foreach ($adminRoles as $adminRole) {
            $permissionNames = $adminRole->permissions()->pluck('name')->toArray();

            $webRole = Role::where('name', $adminRole->name)
                ->where('guard_name', 'web')
                ->where('company_id', $adminRole->company_id)
                ->first();

            $webPermissionNamesActuales = $webRole
                ? $webRole->permissions()->pluck('name')->sort()->values()->toArray()
                : [];

            $webPermissionNamesDestino = Permission::whereIn('name', $permissionNames)
                ->where('guard_name', 'web')
                ->pluck('name')
                ->sort()
                ->values()
                ->toArray();

            if (!$webRole || $webPermissionNamesActuales != $webPermissionNamesDestino) {
                $empresa = $adminRole->company_id ?? 'plantilla global';
                $this->line("  - {$adminRole->name} (empresa {$empresa}): "
                    . ($webRole ? count($webPermissionNamesActuales) : 'sin rol web')
                    . ' -> ' . count($webPermissionNamesDestino) . ' permisos');

                if ($ejecutar) {
                    $webRole = Role::firstOrCreate([
                        'name' => $adminRole->name,
                        'guard_name' => 'web',
                        'company_id' => $adminRole->company_id,
                    ]);
                    $webRole->syncPermissions($webPermissionNamesDestino);
                }

                $corregidos++;
            }
        }

        $this->newLine();
        $this->line("Roles con diferencias: {$corregidos}");

        if ($ejecutar) {
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
            $this->newLine();
            $this->info('Cambios aplicados y caché de permisos limpiada.');
        } else {
            $this->newLine();
            $this->comment('Nada se guardó (modo diagnóstico). Vuelve a ejecutar con --ejecutar para aplicar.');
        }

        return self::SUCCESS;
    }
}
