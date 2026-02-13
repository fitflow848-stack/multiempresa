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
        // Resetear cache de roles/permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ─── Crear permisos ─────────────────────────────────────

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
            'caja.ver',         // Operaciones de POS
            'caja.abrir_cerrar',
            'caja.ajustar',
            'caja.arquear',
            'cajas.ver',        // Gestión de recurso Caja
            'cajas.crear',
            'cajas.editar',
            'cajas.eliminar',
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
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ─── Crear roles ────────────────────────────────────────

        // Super Admin: administrador general del sistema (crea empresas, ve todo)
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);

        // Admin Empresa: administrador de una empresa específica
        $adminEmpresaRole = Role::firstOrCreate(['name' => 'admin_empresa']);

        // Roles operativos (ya existentes)
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $supervisorRole = Role::firstOrCreate(['name' => 'supervisor']);
        $vendedorRole = Role::firstOrCreate(['name' => 'vendedor']);
        $cajeroRole = Role::firstOrCreate(['name' => 'cajero']);

        // ─── Asignar permisos a roles ───────────────────────────

        // Super Admin: TODOS los permisos
        $allPermissions = Permission::all();
        $superAdminRole->syncPermissions($allPermissions);

        // Admin Empresa: gestiona su empresa completa PERO con restricciones de infraestructura (solo ver sucursales/cajas)
        $adminEmpresaRole->syncPermissions($allPermissions->filter(function ($p) {
            $restricted = [
                'empresas.crear',
                'empresas.editar',
                'empresas.eliminar',
                'sucursales.crear',
                'sucursales.editar',
                'sucursales.eliminar',
                'cajas.crear',
                'cajas.editar',
                'cajas.eliminar',
            ];
            return !in_array($p->name, $restricted);
        }));

        // Admin ( legacy )
        $adminRole->syncPermissions($allPermissions);

        // Supervisor: supervisa operaciones en su sucursal
        $supervisorRole->syncPermissions([
            'usuarios.ver',
            'usuarios.crear',
            'usuarios.editar',
            'sucursales.ver',
            'ventas.ver',
            'ventas.crear',
            'ventas.editar',
            'ventas.anular',
            'pos.ver',
            'comprobantes.ver',
            'comprobantes.imprimir',
            'inventario.ver',
            'productos.ver',
            'clientes.ver',
            'clientes.crear',
            'caja.ver',
            'caja.abrir_cerrar',
            'caja.arquear',
            'operaciones_caja.ver',
            'reportes.ver',
        ]);

        // Vendedor: puede ver y crear ventas
        $vendedorRole->syncPermissions([
            'ventas.ver',
            'ventas.crear',
            'ventas.editar',
            'pos.ver',
            'comprobantes.ver',
            'comprobantes.imprimir',
            'productos.ver',
            'clientes.ver',
            'clientes.crear',
            'caja.ver',
        ]);

        // Cajero: solo puede procesar ventas y manejar caja
        $cajeroRole->syncPermissions([
            'ventas.ver',
            'ventas.crear',
            'pos.ver',
            'comprobantes.ver',
            'comprobantes.imprimir',
            'productos.ver',
            'caja.ver',
            'caja.abrir_cerrar',
            'caja.arquear',
            'operaciones_caja.ver',
            'operaciones_caja.crear',
        ]);

        // ─── Asignar rol al primer usuario ──────────────────────

        $adminUser = User::first();
        if ($adminUser) {
            if (!$adminUser->hasAnyRole(['super_admin', 'admin', 'admin_empresa'])) {
                $adminUser->assignRole('super_admin'); // Le damos super_admin por ser el primero
            }

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
