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
        // Permisos organizados por áreas funcionales
        // IMPORTANTE: Cada permiso aquí debe coincidir exactamente con lo que usan
        // las rutas (middleware can:), sidebar (@can) y Filament Resources (canAccess)
        $modules = [
            // === ADMINISTRACIÓN DEL SISTEMA ===
            'usuarios' => ['ver', 'crear', 'editar', 'eliminar'],
            'roles' => ['ver', 'crear', 'editar', 'eliminar'],
            'empresas' => ['ver', 'editar'],
            'sucursales' => ['ver', 'crear', 'editar', 'eliminar'],
            'configuracion' => ['ver', 'editar'],
            
            // === GESTIÓN DE CAJA Y FINANZAS ===
            'cajas' => ['ver', 'crear', 'editar', 'eliminar', 'abrir_cerrar', 'ajustar', 'arquear'],
            'operaciones_caja' => ['eliminar'], // Solo administradores pueden eliminar
            'equipos_caja' => ['ver', 'crear', 'editar', 'eliminar', 'asignar'], // Dispositivos/hardware de caja
            'tesoreria' => ['ver', 'crear', 'editar', 'eliminar'],
            'finanzas' => ['ver', 'crear', 'editar', 'eliminar', 'balance', 'estado_resultados'],
            'deudas' => ['ver', 'pagar', 'reporte'],
            
            // === INVENTARIO Y PRODUCTOS ===
            'productos' => ['ver', 'crear', 'editar', 'eliminar', 'modificar_precio'],
            'inventario' => ['ver', 'crear', 'editar', 'eliminar', 'ajustar', 'kardex', 'transferir'],
            'catalogos' => ['ver', 'gestionar'],
            
            // === CLIENTES Y PROVEEDORES ===
            'clientes' => ['ver', 'crear', 'editar', 'eliminar'],
            'proveedores' => ['ver', 'crear', 'editar', 'eliminar'],
            
            // === VENTAS Y OPERACIONES COMERCIALES ===
            'ventas' => ['ver', 'crear', 'editar', 'eliminar', 'pos'], // POS integrado en ventas
            'cotizaciones' => ['ver', 'crear', 'editar', 'eliminar', 'convertir'],
            'compras' => ['ver', 'crear', 'editar', 'eliminar', 'recibir'],
            'comprobantes' => ['ver', 'imprimir', 'cancelar', 'anular'],
            'guias_remision' => ['ver', 'crear', 'editar', 'eliminar', 'enviar'],
            
            // === CONTABILIDAD ===
            'contabilidad' => ['ver', 'gestionar_activos', 'gestionar_pasivos'],
            
            // === REPORTES Y EXPORTACIONES ===
            'reportes' => ['ver', 'crear', 'editar', 'eliminar', 'exportar'],
        ];

        // Crear permisos para cada módulo
        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => "$module.$action",
                    'guard_name' => 'web',
                ]);
            }
        }

        $this->command->info('Permisos creados correctamente.');
    }
}