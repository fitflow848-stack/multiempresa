<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ExampleRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear rol de ejemplo: Vendedor
        $vendedorRole = Role::firstOrCreate([
            'name' => 'vendedor',
            'guard_name' => 'admin'
        ]);

        // Asignar permisos específicos al vendedor
        $permisos = [
            'usuarios.ver',          // Puede ver usuarios
            'productos.ver',         // Puede ver productos
            'productos.crear',       // Puede crear productos
            'productos.editar',      // Puede editar productos
            'clientes.ver',          // Puede ver clientes
            'clientes.crear',        // Puede crear clientes
            'clientes.editar',       // Puede editar clientes
            'ventas.ver',            // Puede ver ventas
            'ventas.crear',          // Puede realizar ventas
            'cajas.ver',             // Puede ver cajas
            'inventario.ver',        // Puede ver inventario
            'reportes.ver',          // Puede ver reportes básicos
        ];

        foreach ($permisos as $permiso) {
            $permission = Permission::where('name', $permiso)->where('guard_name', 'admin')->first();
            if ($permission) {
                $vendedorRole->givePermissionTo($permission);
            }
        }

        // Crear rol de ejemplo: Cajero
        $cajeroRole = Role::firstOrCreate([
            'name' => 'cajero',
            'guard_name' => 'admin'
        ]);

        // Asignar permisos específicos al cajero
        $permisosCajero = [
            'clientes.ver',          // Puede ver clientes
            'clientes.crear',        // Puede crear clientes
            'ventas.ver',            // Puede ver ventas
            'ventas.crear',          // Puede realizar ventas
            'cajas.ver',             // Puede ver cajas
            'productos.ver',         // Puede consultar productos
            'tesoreria.ver',         // Puede ver tesorería básica
        ];

        foreach ($permisosCajero as $permiso) {
            $permission = Permission::where('name', $permiso)->where('guard_name', 'admin')->first();
            if ($permission) {
                $cajeroRole->givePermissionTo($permission);
            }
        }

        // Crear rol de ejemplo: Contador
        $contadorRole = Role::firstOrCreate([
            'name' => 'contador',
            'guard_name' => 'admin'
        ]);

        // Asignar permisos específicos al contador
        $permisosContador = [
            'finanzas.ver',          // Puede ver finanzas
            'finanzas.balance',      // Puede ver balance
            'finanzas.estado_resultados', // Puede ver estado de resultados
            'reportes.ver',          // Puede ver reportes
            'reportes.crear',        // Puede generar reportes
            'tesoreria.ver',         // Puede ver tesorería
            'compras.ver',           // Puede ver compras
            'inventario.ver',        // Puede ver inventario
            'proveedores.ver',       // Puede ver proveedores
        ];

        foreach ($permisosContador as $permiso) {
            $permission = Permission::where('name', $permiso)->where('guard_name', 'admin')->first();
            if ($permission) {
                $contadorRole->givePermissionTo($permission);
            }
        }

        $this->command->info('Roles de ejemplo creados:');
        $this->command->info('- Vendedor: ' . $vendedorRole->permissions->count() . ' permisos');
        $this->command->info('- Cajero: ' . $cajeroRole->permissions->count() . ' permisos');  
        $this->command->info('- Contador: ' . $contadorRole->permissions->count() . ' permisos');
    }
}