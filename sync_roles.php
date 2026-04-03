<?php

use App\Models\Company;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

// Cargamos el kernel de Laravel para poder usar Eloquent y Facades
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- Iniciando sincronización de roles por empresa ---\n";

DB::transaction(function () {
    // 1. Obtenemos los roles 'plantilla' (aquellos que tienen company_id NULL)
    $templateRoles = Role::whereNull('company_id')->get();
    $companies = Company::all();
    
    $roleMap = []; // Guardará el mapeo [ID_rol_original => [ID_empresa => ID_nuevo_rol]]

    foreach ($companies as $company) {
        $nombreEmpresa = $company->nombre ?? $company->name ?? "(Sin Nombre ID: {$company->id})";
        echo "Empresa: {$nombreEmpresa} (ID: {$company->id})\n";
        foreach ($templateRoles as $template) {
            // Creamos o recuperamos la copia del rol para esta empresa
            $newRole = Role::firstOrCreate([
                'name' => $template->name,
                'guard_name' => $template->guard_name,
                'company_id' => $company->id
            ]);
            
            // Sincronizamos los mismos permisos que tenía el original
            $permissionNames = $template->permissions()->pluck('name')->toArray();
            $newRole->syncPermissions($permissionNames);
            
            $roleMap[$template->id][$company->id] = $newRole->id;
        }
    }

    // 2. Migramos las asignaciones de roles de los usuarios
    // Buscamos registros donde company_id todavía sea NULL en la tabla pivot
    $assignments = DB::table('model_has_roles')->whereNull('company_id')->get();
    
    echo "Actualizando " . count($assignments) . " asignaciones de usuarios...\n";

    foreach ($assignments as $assignment) {
        $user = User::find($assignment->model_id);
        if (!$user || !$user->company_id) continue;

        $oldRoleId = $assignment->role_id;
        $companyId = $user->company_id;

        if (isset($roleMap[$oldRoleId][$companyId])) {
            $newRoleId = $roleMap[$oldRoleId][$companyId];

            // Reemplazamos el rol genérico por el rol de su empresa en el pivot
            DB::table('model_has_roles')
                ->where('role_id', $oldRoleId)
                ->where('model_id', $user->id)
                ->where('model_type', $assignment->model_type)
                ->whereNull('company_id')
                ->update([
                    'role_id' => $newRoleId,
                    'company_id' => $companyId
                ]);
        }
    }
    
    echo "--- Sincronización completada con éxito ---\n";
});

// Limpiamos el caché de permisos al finalizar
Artisan::call('permission:cache-reset');
echo "Caché de permisos reiniciado.\n";
