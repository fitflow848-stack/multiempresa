<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CheckUserPermissions extends Command
{
    protected $signature = 'check:user {email}';
    protected $description = 'Check user roles and permissions';

    public function handle()
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("Usuario {$email} no encontrado");
            return;
        }
        
        $this->info("Usuario: {$user->name} ({$user->email})");
        $this->info("Company ID: {$user->company_id}");
        
        $this->info("\nRoles:");
        foreach ($user->roles as $role) {
            $this->line("  - {$role->name} (guard: {$role->guard_name})");
            
            // Mostrar permisos del rol
            $permissions = $role->permissions;
            $this->line("    Permisos del rol: {$permissions->count()}");
            if ($permissions->count() > 0) {
                foreach ($permissions->take(10) as $permission) {
                    $this->line("      • {$permission->name}");
                }
                if ($permissions->count() > 10) {
                    $this->line("      ... y " . ($permissions->count() - 10) . " más");
                }
            }
        }
        
        $this->info("\nPermisos directos: " . $user->permissions->count());
        $this->info("Total permisos via roles: " . $user->getPermissionsViaRoles()->count());
        
        // Verificar permisos específicos
        $testPermissions = [
            'usuarios.ver',
            'productos.ver', 
            'productos.modificar_precio',   // Nuevo permiso agregado
            'ventas.ver',
            'tesoreria.ver',         // Cambiado de 'finanzas.ver' 
            'cajas.ver'
        ];
        
        $this->info("\nVerificación de permisos específicos:");
        foreach ($testPermissions as $permission) {
            // Simular autenticación en guard admin para testing
            auth('admin')->login($user);
            
            $hasPermissionAdmin = $user->can($permission);
            $hasPermissionDirect = $user->hasPermissionTo($permission, 'admin');
            
            $statusAdmin = $hasPermissionAdmin ? '✓' : '✗';
            $statusDirect = $hasPermissionDirect ? '✓' : '✗';
            
            $this->line("  {$statusAdmin} {$permission} (via can())");
            $this->line("  {$statusDirect} {$permission} (via hasPermissionTo())");
            
            // Logout para el siguiente test
            auth('admin')->logout();
        }
    }
}