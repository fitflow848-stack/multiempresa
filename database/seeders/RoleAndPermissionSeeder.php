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
        // Crear permisos en español
        $permissions = [
            // Administración
            'usuarios.ver',
            'usuarios.crear',
            'usuarios.editar',
            'usuarios.eliminar',
            'empresas.ver',
            'empresas.crear',
            'empresas.editar',
            'empresas.eliminar',
            'roles.ver',
            'roles.crear',
            'roles.editar',
            'roles.eliminar',
            'sucursales.ver',
            'sucursales.crear',
            'sucursales.editar',
            'sucursales.eliminar',

            // Ventas y POS
            'ventas.ver',
            'ventas.crear',
            'ventas.editar',
            'ventas.eliminar',
            'ventas.anular',
            'pos.ver',
            'comprobantes.ver',
            'comprobantes.cancelar',
            'comprobantes.anular',
            'comprobantes.imprimir',

            // Inventario
            'inventario.ver',
            'inventario.ajustar',
            'inventario.transferir',
            'inventario.kardex',
            'productos.ver',
            'productos.crear',
            'productos.editar',
            'productos.eliminar',

            // Compras y Proveedores
            'compras.ver',
            'compras.crear',
            'compras.editar',
            'compras.eliminar',
            'compras.recibir',
            'proveedores.ver',
            'proveedores.crear',
            'proveedores.editar',
            'proveedores.eliminar',

            // Clientes y Deudas
            'clientes.ver',
            'clientes.crear',
            'clientes.editar',
            'clientes.eliminar',
            'deudas.ver',
            'deudas.pagar',
            'deudas.reporte',

            // Caja y Operaciones
            'caja.ver',
            'caja.abrir_cerrar',
            'caja.ajustar',
            'caja.arquear',
            'operaciones_caja.ver',
            'operaciones_caja.ver_todo',
            'operaciones_caja.crear',

            // Documentos
            'guias_remision.ver',
            'guias_remision.crear',
            'guias_remision.editar',
            'guias_remision.eliminar',
            'guias_remision.enviar',
            'cotizaciones.ver',
            'cotizaciones.crear',
            'cotizaciones.editar',
            'cotizaciones.eliminar',
            'cotizaciones.convertir',

            // Contabilidad y Finanzas
            'contabilidad.ver',
            'contabilidad.gestionar_activos',
            'contabilidad.gestionar_pasivos',

            // Catálogos (Marcas, Familias, etc.)
            'catalogos.ver',
            'catalogos.gestionar',

            // Reportes
            'reportes.ver',
            'reportes.exportar',

            // Configuración del sistema
            'configuracion.ver',
            'configuracion.editar',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Crear roles
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $supervisorRole = Role::firstOrCreate(['name' => 'supervisor', 'guard_name' => 'web']);
        $vendedorRole = Role::firstOrCreate(['name' => 'vendedor', 'guard_name' => 'web']);
        $cajeroRole = Role::firstOrCreate(['name' => 'cajero', 'guard_name' => 'web']);

        // Asignar permisos a roles
        // Admin tiene todos los permisos
        $adminRole->syncPermissions(Permission::all());

        // Supervisor tiene la mayoría de permisos excepto algunos críticos
        $supervisorRole->syncPermissions([
            'usuarios.ver',
            'usuarios.crear',
            'usuarios.editar',
            'empresas.ver',
            'sucursales.ver',
            'ventas.ver',
            'ventas.crear',
            'ventas.editar',
            'productos.ver',
            'productos.crear',
            'productos.editar',
            'reportes.ver',
            'reportes.exportar',
            'inventario.ver',
            'clientes.ver',
            'proveedores.ver',
        ]);

        // Vendedor puede ver y crear ventas
        $vendedorRole->syncPermissions([
            'ventas.ver',
            'ventas.crear',
            'ventas.editar',
            'productos.ver',
            'pos.ver',
        ]);

        // Cajero solo puede procesar ventas y ver caja
        $cajeroRole->syncPermissions([
            'ventas.ver',
            'ventas.crear',
            'productos.ver',
            'pos.ver',
            'caja.ver',
            'caja.abrir_cerrar',
        ]);

        // Asignar el rol de admin al primer usuario creado y asignar empresa
        $adminUser = User::first();
        if ($adminUser) {
            $adminUser->assignRole('admin');

            // Si no tiene company_id, asignar la primera empresa
            if (!$adminUser->company_id) {
                $company = \App\Models\Company::first();
                if ($company) {
                    $adminUser->update(['company_id' => $company->id]);
                }
            }
        }
    }
}
