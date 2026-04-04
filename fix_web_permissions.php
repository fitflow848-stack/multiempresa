<?php

/**
 * Script de migración segura para producción.
 * Solo AGREGA los permisos faltantes al guard 'web' y los asigna
 * a los roles que ya los tienen en 'admin'. NO modifica roles existentes.
 *
 * Ejecutar con: php artisan tinker --execute="require 'fix_web_permissions.php';"
 * O directamente: php fix_web_permissions.php
 */

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

$created = 0;
$assigned = 0;

// 1. Encontrar permisos que existen en 'admin' pero no en 'web'
$adminPerms = Permission::where('guard_name', 'admin')->get();

foreach ($adminPerms as $adminPerm) {
    $webPerm = Permission::firstOrCreate(
        ['name' => $adminPerm->name, 'guard_name' => 'web']
    );

    if ($webPerm->wasRecentlyCreated) {
        $created++;
        echo "✓ Creado: {$adminPerm->name} (web)\n";
    }
}

// 2. Para cada rol en 'web', asignarle los permisos que tiene
//    su contraparte en 'admin' (solo agrega, no quita nada)
$webRoles = Role::where('guard_name', 'web')->get();

foreach ($webRoles as $webRole) {
    $adminRole = Role::where('name', $webRole->name)
        ->where('guard_name', 'admin')
        ->first();

    if (!$adminRole) continue;

    $adminRolePermNames = $adminRole->permissions->pluck('name');
    $webRolePermNames   = $webRole->permissions->pluck('name');

    $missing = $adminRolePermNames->diff($webRolePermNames);

    foreach ($missing as $permName) {
        $webPerm = Permission::where('name', $permName)->where('guard_name', 'web')->first();
        if ($webPerm) {
            $webRole->permissions()->attach($webPerm);
            $assigned++;
            echo "  + Asignado '{$permName}' al rol '{$webRole->name}' (web)\n";
        }
    }
}

app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

echo "\n=== Listo ===\n";
echo "Permisos creados en web: {$created}\n";
echo "Asignaciones de permisos a roles: {$assigned}\n";
