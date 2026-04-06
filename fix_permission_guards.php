<?php

/**
 * Script para corregir el problema de guards en el sistema de permisos
 * Ejecutar: php fix_permission_guards.php
 */

require_once __DIR__ . '/vendor/autoload.php';

// Cargar la aplicación Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

echo "=== CORRECCIÓN DE PERMISOS Y GUARDS ===\n\n";

try {
    // 1. Limpiar permisos existentes con guard incorrecto
    echo "1. Limpiando permisos con guard 'admin'...\n";
    $oldPermissions = Permission::where('guard_name', 'admin')->get();
    echo "Encontrados " . $oldPermissions->count() . " permisos con guard 'admin'\n";
    
    foreach ($oldPermissions as $permission) {
        $permission->delete();
        echo "- Eliminado: " . $permission->name . "\n";
    }

    // 2. Limpiar roles existentes con guard incorrecto
    echo "\n2. Limpiando roles con guard 'admin'...\n";
    $oldRoles = Role::where('guard_name', 'admin')->get();
    echo "Encontrados " . $oldRoles->count() . " roles con guard 'admin'\n";
    
    foreach ($oldRoles as $role) {
        $role->delete();
        echo "- Eliminado: " . $role->name . "\n";
    }

    // 3. Ejecutar seeders para crear permisos y roles correctos
    echo "\n3. Recreando permisos con guard correcto...\n";
    Artisan::call('db:seed', ['--class' => 'PermissionsSeeder']);
    echo "✅ Permisos creados\n";

    echo "\n4. Recreando roles con guard correcto...\n";
    Artisan::call('db:seed', ['--class' => 'ExampleRoleSeeder']);
    echo "✅ Roles creados\n";

    // 5. Verificar resultado
    echo "\n5. Verificando resultado...\n";
    $webPermissions = Permission::where('guard_name', 'web')->count();
    $webRoles = Role::where('guard_name', 'web')->count();
    
    echo "Permisos con guard 'web': " . $webPermissions . "\n";
    echo "Roles con guard 'web': " . $webRoles . "\n";

    // Mostrar permisos del vendedor
    $vendedorRole = Role::where('name', 'vendedor')->where('guard_name', 'web')->first();
    if ($vendedorRole) {
        echo "\nPermisos del rol 'vendedor':\n";
        foreach ($vendedorRole->permissions as $permission) {
            echo "- " . $permission->name . "\n";
        }
        
        // Verificar permisos específicos para pagos
        $tieneFinanzasCrear = $vendedorRole->hasPermissionTo('finanzas.crear');
        echo "\n¿Vendedor puede crear finanzas (registrar pagos)? " . ($tieneFinanzasCrear ? "✅ SÍ" : "❌ NO") . "\n";
    }

    echo "\n🎉 CORRECCIÓN COMPLETADA EXITOSAMENTE\n";
    echo "\nAhora los vendedores deberían poder registrar pagos.\n";

} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Línea: " . $e->getLine() . " en archivo: " . $e->getFile() . "\n";
}

echo "\n=== FIN DEL SCRIPT ===\n";