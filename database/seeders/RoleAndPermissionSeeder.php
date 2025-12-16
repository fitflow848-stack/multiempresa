<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear permisos
        $permissions = [
            // Módulo de usuarios
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            
            // Módulo de empresas
            'companies.view',
            'companies.create',
            'companies.edit',
            'companies.delete',
            
            // Módulo de roles
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',
            
            // Módulo de ventas
            'sales.view',
            'sales.create',
            'sales.edit',
            'sales.delete',
            
            // Módulo de productos
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',
            
            // Módulo de reportes
            'reports.view',
            'reports.export',
            
            // Configuración del sistema
            'settings.view',
            'settings.edit',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Crear roles
        $adminRole = Role::create(['name' => 'admin']);
        $supervisorRole = Role::create(['name' => 'supervisor']);
        $vendedorRole = Role::create(['name' => 'vendedor']);
        $cajeroRole = Role::create(['name' => 'cajero']);

        // Asignar permisos a roles
        // Admin tiene todos los permisos
        $adminRole->givePermissionTo(Permission::all());

        // Supervisor tiene la mayoría de permisos excepto algunos críticos
        $supervisorRole->givePermissionTo([
            'users.view',
            'users.create',
            'users.edit',
            'companies.view',
            'sales.view',
            'sales.create',
            'sales.edit',
            'products.view',
            'products.create',
            'products.edit',
            'reports.view',
            'reports.export',
        ]);

        // Vendedor puede ver y crear ventas
        $vendedorRole->givePermissionTo([
            'sales.view',
            'sales.create',
            'sales.edit',
            'products.view',
        ]);

        // Cajero solo puede procesar ventas
        $cajeroRole->givePermissionTo([
            'sales.view',
            'sales.create',
            'products.view',
        ]);

        // Asignar el rol de admin al primer usuario creado
        $adminUser = User::first();
        if ($adminUser) {
            $adminUser->assignRole('admin');
        }
    }
}
