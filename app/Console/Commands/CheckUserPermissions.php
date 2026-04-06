<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class CheckUserPermissions extends Command
{
    protected $signature = 'permissions:check 
                            {user? : Email del usuario a verificar}
                            {--role= : Verificar permisos de un rol específico}
                            {--assign-role= : Asignar rol a usuario}
                            {--fix-guards : Corregir guards de admin a web}';

    protected $description = 'Verificar y gestionar permisos de usuarios y roles';

    public function handle()
    {
        if ($this->option('fix-guards')) {
            return $this->fixGuards();
        }

        if ($role = $this->option('role')) {
            return $this->checkRolePermissions($role);
        }

        if ($userEmail = $this->argument('user')) {
            return $this->checkUserPermissions($userEmail);
        }

        // Si no se específica nada, mostrar resumen general
        $this->showGeneralStatus();
    }

    private function fixGuards()
    {
        $this->info('🔧 Corrigiendo guards de permisos...');
        
        // Limpiar permisos con guard incorrecto
        $oldPermissions = Permission::where('guard_name', 'admin')->count();
        Permission::where('guard_name', 'admin')->delete();
        $this->info("Eliminados {$oldPermissions} permisos con guard 'admin'");

        // Limpiar roles con guard incorrecto
        $oldRoles = Role::where('guard_name', 'admin')->count();
        Role::where('guard_name', 'admin')->delete();
        $this->info("Eliminados {$oldRoles} roles con guard 'admin'");

        // Recrear permisos y roles
        $this->call('db:seed', ['--class' => 'PermissionsSeeder']);
        $this->call('db:seed', ['--class' => 'ExampleRoleSeeder']);

        $this->info('✅ Guards corregidos exitosamente');
        
        // Verificar vendedor puede registrar pagos
        $vendedor = Role::where('name', 'vendedor')->where('guard_name', 'web')->first();
        if ($vendedor && $vendedor->hasPermissionTo('finanzas.crear')) {
            $this->info('✅ Vendedor ahora puede registrar pagos');
        } else {
            $this->error('❌ Vendedor aún no puede registrar pagos');
        }
        
        return 0;
    }

    private function checkRolePermissions($roleName)
    {
        $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();
        
        if (!$role) {
            $this->error("No se encontró el rol '{$roleName}' con guard 'web'");
            return 1;
        }

        $this->info("📋 Permisos del rol '{$roleName}':");
        
        $permissions = $role->permissions;
        if ($permissions->isEmpty()) {
            $this->warn('Este rol no tiene permisos asignados');
        } else {
            foreach ($permissions as $permission) {
                $this->line("  • {$permission->name}");
            }
        }

        // Verificaciones específicas para vendedor
        if ($roleName === 'vendedor') {
            $this->info("\n🔍 Verificaciones específicas para vendedor:");
            $this->checkSpecificPermissions($role, [
                'finanzas.crear' => 'Registrar pagos',
                'ventas.crear' => 'Crear ventas',
                'cajas.abrir_cerrar' => 'Abrir/cerrar cajas'
            ]);
        }

        return 0;
    }

    private function checkUserPermissions($email)
    {
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("No se encontró el usuario con email '{$email}'");
            return 1;
        }

        $this->info("👤 Usuario: {$user->name} ({$user->email})");
        
        // Mostrar roles
        $roles = $user->getRoleNames();
        if ($roles->isEmpty()) {
            $this->warn('Usuario sin roles asignados');
            
            if ($this->option('assign-role')) {
                $this->assignRoleToUser($user);
            }
        } else {
            $this->info("🎭 Roles: " . $roles->implode(', '));
        }

        // Mostrar permisos
        $permissions = $user->getAllPermissions();
        if ($permissions->isEmpty()) {
            $this->warn('Usuario sin permisos');
        } else {
            $this->info("\n📋 Permisos efectivos:");
            foreach ($permissions as $permission) {
                $this->line("  • {$permission->name}");
            }
        }

        // Verificar permisos críticos
        $this->info("\n🔍 Verificaciones específicas:");
        $this->checkSpecificPermissions($user, [
            'finanzas.crear' => 'Registrar pagos',
            'ventas.crear' => 'Crear ventas',
            'contabilidad.gestionar_pasivos' => 'Gestionar pasivos'
        ]);

        return 0;
    }

    private function checkSpecificPermissions($entity, $permissionsToCheck)
    {
        foreach ($permissionsToCheck as $permission => $description) {
            $hasPermission = $entity->hasPermissionTo($permission);
            $status = $hasPermission ? '✅' : '❌';
            $this->line("  {$status} {$description} ({$permission})");
        }
    }

    private function assignRoleToUser($user)
    {
        $roleName = $this->option('assign-role');
        $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();
        
        if (!$role) {
            $this->error("No se encontró el rol '{$roleName}'");
            return;
        }

        $user->assignRole($role);
        $this->info("✅ Rol '{$roleName}' asignado a {$user->name}");
    }

    private function showGeneralStatus()
    {
        $this->info('📊 Estado general del sistema de permisos:');
        
        // Contar permisos por guard
        $webPermissions = Permission::where('guard_name', 'web')->count();
        $adminPermissions = Permission::where('guard_name', 'admin')->count();
        
        $this->line("  • Permisos con guard 'web': {$webPermissions}");
        $this->line("  • Permisos con guard 'admin': {$adminPermissions}");
        
        if ($adminPermissions > 0) {
            $this->warn('  ⚠️  Hay permisos con guard incorrecto. Ejecuta --fix-guards');
        }

        // Contar roles
        $webRoles = Role::where('guard_name', 'web')->count();
        $adminRoles = Role::where('guard_name', 'admin')->count();
        
        $this->line("  • Roles con guard 'web': {$webRoles}");
        $this->line("  • Roles con guard 'admin': {$adminRoles}");

        // Mostrar roles disponibles
        $roles = Role::where('guard_name', 'web')->pluck('name');
        if ($roles->isNotEmpty()) {
            $this->info("\n🎭 Roles disponibles: " . $roles->implode(', '));
        }

        $this->info("\n💡 Comandos útiles:");
        $this->line("  php artisan permissions:check --role=vendedor");
        $this->line("  php artisan permissions:check usuario@email.com");
        $this->line("  php artisan permissions:check --fix-guards");
    }
}