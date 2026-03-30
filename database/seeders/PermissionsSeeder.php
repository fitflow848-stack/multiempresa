<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lista de permisos para cada módulo
        $modules = [
            'usuarios' => ['ver', 'crear', 'editar', 'eliminar'],
            'roles' => ['ver', 'crear', 'editar', 'eliminar'],
            'empresas' => ['ver', 'crear', 'editar', 'eliminar'],
            'sucursales' => ['ver', 'crear', 'editar', 'eliminar'],
            'cajas' => ['ver', 'crear', 'editar', 'eliminar'],
            'tesoreria' => ['ver', 'crear', 'editar', 'eliminar'],
            'productos' => ['ver', 'crear', 'editar', 'eliminar'],
            'clientes' => ['ver', 'crear', 'editar', 'eliminar'],
            'proveedores' => ['ver', 'crear', 'editar', 'eliminar'],
            'ventas' => ['ver', 'crear', 'editar', 'eliminar'],
            'compras' => ['ver', 'crear', 'editar', 'eliminar'],
            'inventario' => ['ver', 'crear', 'editar', 'eliminar'],
            'reportes' => ['ver', 'crear', 'editar', 'eliminar'],
            'finanzas' => ['ver', 'crear', 'editar', 'eliminar', 'balance', 'estado_resultados'],
            'configuracion' => ['ver', 'editar'],
            'pos' => ['ver', 'crear', 'editar'],  // Permisos de POS agregados
        ];

        // Crear permisos para cada módulo
        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => "$module.$action",
                    'guard_name' => 'admin',
                ]);
            }
        }

        $this->command->info('Permisos creados correctamente.');
    }
}