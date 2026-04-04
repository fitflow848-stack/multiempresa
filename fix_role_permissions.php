<?php
/**
 * fix_role_permissions.php
 * Repara los permisos de los roles por empresa.
 * 
 * Problema: sync_roles.php copió roles de las plantillas (company_id=NULL)
 * pero las plantillas de vendedor/cajero/contador tenían 0 permisos en ese momento.
 * 
 * Solución: Para cada empresa, asignar los permisos correctos según el nombre del rol.
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use App\Models\Company;

echo "=== Reparación de permisos por empresa ===\n\n";

// ── 1. Definición canónica de permisos por rol ────────────────────────────

$rolePermissions = [

    // Roles de sistema – reciben TODOS los permisos
    'admin'         => '__ALL__',
    'administrador' => '__ALL__',
    'super_admin'   => '__ALL__',
    'admin_empresa' => '__ALL__',  // el admin de empresa también recibe todo

    // Supervisor – todo excepto gestión de usuarios/roles/empresas/sucursales
    'supervisor' => [
        'usuarios.ver',
        'cajas.ver', 'cajas.crear', 'cajas.editar', 'cajas.abrir_cerrar', 'cajas.ajustar', 'cajas.arquear',
        'operaciones_caja.eliminar',
        'equipos_caja.ver', 'equipos_caja.crear', 'equipos_caja.editar', 'equipos_caja.asignar',
        'tesoreria.ver', 'tesoreria.crear', 'tesoreria.editar',
        'finanzas.ver', 'finanzas.balance', 'finanzas.estado_resultados',
        'deudas.ver', 'deudas.pagar', 'deudas.reporte',
        'productos.ver', 'productos.crear', 'productos.editar', 'productos.modificar_precio',
        'inventario.ver', 'inventario.crear', 'inventario.editar', 'inventario.ajustar', 'inventario.kardex', 'inventario.transferir',
        'catalogos.ver', 'catalogos.gestionar',
        'clientes.ver', 'clientes.crear', 'clientes.editar',
        'proveedores.ver', 'proveedores.crear', 'proveedores.editar',
        'ventas.ver', 'ventas.crear', 'ventas.editar', 'ventas.pos',
        'cotizaciones.ver', 'cotizaciones.crear', 'cotizaciones.editar', 'cotizaciones.convertir',
        'compras.ver', 'compras.crear', 'compras.editar', 'compras.recibir',
        'comprobantes.ver', 'comprobantes.imprimir', 'comprobantes.cancelar', 'comprobantes.anular',
        'guias_remision.ver', 'guias_remision.crear', 'guias_remision.editar',
        'reportes.ver', 'reportes.crear', 'reportes.exportar',
    ],

    // Vendedor
    'vendedor' => [
        'usuarios.ver',
        'productos.ver', 'productos.crear', 'productos.editar',
        'inventario.ver', 'inventario.kardex', 'inventario.transferir',
        'clientes.ver', 'clientes.crear', 'clientes.editar',
        'cotizaciones.ver', 'cotizaciones.crear', 'cotizaciones.convertir',
        'ventas.ver', 'ventas.crear', 'ventas.pos',
        'comprobantes.ver', 'comprobantes.imprimir',
        'cajas.ver', 'cajas.abrir_cerrar',
        'equipos_caja.ver',
        'tesoreria.ver',
        'reportes.ver',
    ],

    // Cajero
    'cajero' => [
        'clientes.ver', 'clientes.crear',
        'ventas.ver', 'ventas.crear', 'ventas.pos',
        'productos.ver',
        'comprobantes.ver', 'comprobantes.imprimir',
        'cajas.ver', 'cajas.abrir_cerrar', 'cajas.ajustar', 'cajas.arquear',
        'equipos_caja.ver', 'equipos_caja.asignar',
        'tesoreria.ver',
        'reportes.ver',
    ],

    // Contador
    'contador' => [
        'finanzas.ver', 'finanzas.balance', 'finanzas.estado_resultados',
        'finanzas.crear', 'finanzas.editar',
        'contabilidad.ver', 'contabilidad.gestionar_activos', 'contabilidad.gestionar_pasivos',
        'deudas.ver', 'deudas.pagar', 'deudas.reporte',
        'tesoreria.ver', 'tesoreria.crear',
        'cajas.ver',
        'compras.ver', 'compras.crear', 'compras.recibir',
        'proveedores.ver', 'proveedores.crear',
        'inventario.ver', 'inventario.kardex',
        'productos.ver',
        'comprobantes.ver', 'comprobantes.imprimir', 'comprobantes.cancelar', 'comprobantes.anular',
        'reportes.ver', 'reportes.crear', 'reportes.exportar',
        'guias_remision.ver', 'guias_remision.crear',
    ],

    // Jefe de almacén
    'jefe_almacen' => [
        'productos.ver', 'productos.crear', 'productos.editar', 'productos.modificar_precio',
        'inventario.ver', 'inventario.crear', 'inventario.editar', 'inventario.ajustar', 'inventario.kardex', 'inventario.transferir',
        'catalogos.ver', 'catalogos.gestionar',
        'proveedores.ver', 'proveedores.crear', 'proveedores.editar',
        'compras.ver', 'compras.crear', 'compras.recibir',
        'reportes.ver', 'reportes.exportar',
        'guias_remision.ver', 'guias_remision.crear',
    ],
];

// ── 2. Obtener todos los permisos del guard admin ─────────────────────────

$allPermissions = Permission::where('guard_name', 'admin')->pluck('name')->toArray();
echo "Total de permisos encontrados (guard admin): " . count($allPermissions) . "\n\n";

// ── 3. Determinar qué roles de empresa existen ────────────────────────────

$companies = Company::all();
$totalFixed = 0;

foreach ($companies as $company) {
    $nombre = $company->razon_social ?? "Empresa #{$company->id}";
    echo "Empresa: {$nombre} (ID: {$company->id})\n";

    // Obtener todos los roles de esta empresa (guard admin)
    $companyRoles = Role::where('guard_name', 'admin')
        ->where('company_id', $company->id)
        ->get();

    foreach ($companyRoles as $role) {
        $definition = $rolePermissions[$role->name] ?? null;

        if ($definition === null) {
            echo "  [SKIP] '{$role->name}' — sin definición canónica\n";
            continue;
        }

        $targetPermissions = ($definition === '__ALL__') ? $allPermissions : $definition;

        // Filtrar solo permisos que existen en la BD
        $validPermissions = array_filter($targetPermissions, fn($p) => in_array($p, $allPermissions));

        $currentCount = $role->permissions()->count();
        $role->syncPermissions($validPermissions);
        $newCount = count($validPermissions);

        echo "  [{$role->name}] {$currentCount} → {$newCount} permisos\n";
        $totalFixed++;
    }
    echo "\n";
}

// ── 4. También actualizar las plantillas (company_id NULL) ────────────────

echo "Actualizando roles plantilla (company_id NULL)...\n";
$templateRoles = Role::where('guard_name', 'admin')->whereNull('company_id')->get();
foreach ($templateRoles as $role) {
    $definition = $rolePermissions[$role->name] ?? null;
    if ($definition === null) continue;
    $targetPermissions = ($definition === '__ALL__') ? $allPermissions : $definition;
    $validPermissions = array_filter($targetPermissions, fn($p) => in_array($p, $allPermissions));
    $role->syncPermissions($validPermissions);
    echo "  [plantilla:{$role->name}] → " . count($validPermissions) . " permisos\n";
}

// ── 5. Limpiar asignaciones huérfanas en model_has_roles ─────────────────

echo "\nLimpiando asignaciones model_has_roles con company_id NULL de usuarios con empresa...\n";

$orphans = DB::table('model_has_roles')
    ->join('users', 'users.id', '=', 'model_has_roles.model_id')
    ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
    ->whereNull('model_has_roles.company_id')
    ->whereNotNull('users.company_id')
    ->where('roles.guard_name', 'admin')
    ->select(
        'model_has_roles.model_id',
        'model_has_roles.role_id',
        'model_has_roles.model_type',
        'users.company_id as user_company',
        'roles.name as role_name',
        'roles.company_id as role_company'
    )
    ->get();

echo "Encontradas " . count($orphans) . " asignaciones con company_id NULL.\n";

foreach ($orphans as $orphan) {
    // Buscar el rol equivalente de la empresa del usuario
    $correctRole = Role::where('name', $orphan->role_name)
        ->where('guard_name', 'admin')
        ->where('company_id', $orphan->user_company)
        ->first();

    if ($correctRole) {
        // Eliminar la asignación huérfana
        DB::table('model_has_roles')
            ->where('model_id', $orphan->model_id)
            ->where('role_id', $orphan->role_id)
            ->where('model_type', $orphan->model_type)
            ->whereNull('company_id')
            ->delete();

        // Insertar la asignación correcta si no existe
        $exists = DB::table('model_has_roles')
            ->where('model_id', $orphan->model_id)
            ->where('role_id', $correctRole->id)
            ->where('model_type', $orphan->model_type)
            ->where('company_id', $orphan->user_company)
            ->exists();

        if (!$exists) {
            DB::table('model_has_roles')->insert([
                'model_id'   => $orphan->model_id,
                'role_id'    => $correctRole->id,
                'model_type' => $orphan->model_type,
                'company_id' => $orphan->user_company,
            ]);
            echo "  Corregida asignación: user#{$orphan->model_id} → {$orphan->role_name} (company:{$orphan->user_company})\n";
        } else {
            echo "  Eliminada asignación duplicada/huérfana: user#{$orphan->model_id} → {$orphan->role_name}\n";
        }
    } else {
        echo "  [WARN] No se encontró rol '{$orphan->role_name}' para empresa {$orphan->user_company}\n";
    }
}

// ── 6. Limpiar duplicados en model_has_roles ─────────────────────────────

echo "\nEliminando asignaciones duplicadas en model_has_roles...\n";
$duplicates = DB::table('model_has_roles')
    ->select('model_id', 'role_id', 'model_type', 'company_id')
    ->groupBy('model_id', 'role_id', 'model_type', 'company_id')
    ->havingRaw('COUNT(*) > 1')
    ->get();

foreach ($duplicates as $dup) {
    // Conservar solo 1 registro, eliminar los demás
    $ids = DB::table('model_has_roles')
        ->where('model_id', $dup->model_id)
        ->where('role_id', $dup->role_id)
        ->where('model_type', $dup->model_type)
        ->where('company_id', $dup->company_id)
        ->pluck('id')
        ->skip(1)
        ->values();

    if ($ids->count() > 0) {
        DB::table('model_has_roles')->whereIn('id', $ids)->delete();
        echo "  Eliminados " . $ids->count() . " duplicados: model_id={$dup->model_id} role_id={$dup->role_id}\n";
    }
}

// ── 7. Limpiar caché ──────────────────────────────────────────────────────

echo "\nLimpiando caché de permisos...\n";
Artisan::call('permission:cache-reset');
echo "Caché limpiado.\n";

echo "\n=== Reparación completada. Roles reparados: {$totalFixed} ===\n";
