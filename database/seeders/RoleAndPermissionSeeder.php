<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\Company;

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
            'inventario.crear',
            'inventario.editar',
            'inventario.eliminar',
            'inventario.ajustar',
            'inventario.transferir',
            'inventario.kardex',
            'productos.ver',
            'productos.crear',
            'productos.editar',
            'productos.eliminar',
            'productos.modificar_precio',

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
            'cajas.abrir_cerrar',
            'cajas.ajustar',
            'cajas.arquear',
            'operaciones_caja.ver',
            'operaciones_caja.ver_todo',
            'operaciones_caja.crear',
            'operaciones_caja.eliminar',

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
            'finanzas.ver',
            'finanzas.crear',
            'finanzas.editar',
            'finanzas.eliminar',
            'finanzas.balance',
            'finanzas.estado_resultados',
            'bancos.ver',
            'tesoreria.ver',
            'tesoreria.crear',
            'tesoreria.editar',
            'tesoreria.eliminar',

            // Catálogos (Marcas, Familias, etc.)
            'catalogos.ver',
            'catalogos.gestionar',

            // Reportes
            'reportes.ver',
            'reportes.crear',
            'reportes.editar',
            'reportes.eliminar',
            'reportes.exportar',

            // Configuración del sistema
            'configuracion.ver',
            'configuracion.editar',
        ];

        $guards = ['web', 'admin'];

        // Recopilar todos los nombres de permisos únicos (predefinidos + existentes)
        $allUniqueNames = collect($permissions)
            ->merge(Permission::pluck('name'))
            ->unique();

        foreach ($allUniqueNames as $permissionName) {
            foreach ($guards as $guard) {
                Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => $guard]);
            }
        }

        // ─── Crear roles ────────────────────────────────────────

        foreach ($guards as $guard) {
            // Super Admin
            $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => $guard]);
            $allPermissions = Permission::where('guard_name', $guard)->get();
            $superAdminRole->syncPermissions($allPermissions);

            // Admin Empresa
            $adminEmpresaRole = Role::firstOrCreate(['name' => 'admin_empresa', 'guard_name' => $guard]);
            $adminEmpresaRole->syncPermissions($allPermissions->filter(function ($p) {
                $soloSuperAdmin = [
                    'empresas.crear',
                    'empresas.eliminar',
                    'sucursales.crear',
                    'sucursales.editar',
                    'sucursales.eliminar',
                ];
                return !in_array($p->name, $soloSuperAdmin);
            }));

            // Supervisor
            $supervisorRole = Role::firstOrCreate(['name' => 'supervisor', 'guard_name' => $guard]);
            $supervisorRole->syncPermissions([
                'usuarios.ver', 'usuarios.crear', 'usuarios.editar', 'sucursales.ver',
                'ventas.ver', 'ventas.crear', 'ventas.editar', 'ventas.anular',
                'pos.ver', 'comprobantes.ver', 'comprobantes.imprimir',
                'inventario.ver', 'productos.ver', 'clientes.ver', 'clientes.crear',
                'caja.ver', 'caja.abrir_cerrar', 'caja.arquear', 'operaciones_caja.ver', 'reportes.ver'
            ]);

            // Jefe de Almacén
            $jefeAlmacenRole = Role::firstOrCreate(['name' => 'jefe_almacen', 'guard_name' => $guard]);
            $jefeAlmacenRole->syncPermissions([
                'productos.ver', 'productos.crear', 'productos.editar', 'productos.eliminar',
                'inventario.ver', 'inventario.ajustar', 'inventario.transferir', 'inventario.kardex',
                'compras.ver', 'compras.crear', 'compras.recibir', 'catalogos.ver', 'catalogos.gestionar', 'sucursales.ver',
            ]);

            // Vendedor
            $vendedorRole = Role::firstOrCreate(['name' => 'vendedor', 'guard_name' => $guard]);
            $vendedorRole->syncPermissions([
                'ventas.ver', 'ventas.crear', 'ventas.editar', 'pos.ver',
                'comprobantes.ver', 'comprobantes.imprimir', 'productos.ver', 'clientes.ver', 'clientes.crear', 'caja.ver'
            ]);

            // Cajero
            $cajeroRole = Role::firstOrCreate(['name' => 'cajero', 'guard_name' => $guard]);
            $cajeroRole->syncPermissions([
                'ventas.ver', 'ventas.crear', 'pos.ver', 'comprobantes.ver', 'comprobantes.imprimir',
                'productos.ver', 'caja.ver', 'caja.abrir_cerrar', 'caja.arquear', 'operaciones_caja.ver', 'operaciones_caja.crear'
            ]);

            // Roles legacy
            $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => $guard]);
            $adminRole->syncPermissions($adminEmpresaRole->permissions);

            $administradorRole = Role::firstOrCreate(['name' => 'administrador', 'guard_name' => $guard]);
            $administradorRole->syncPermissions($adminEmpresaRole->permissions);
        }

        // ─── Asignar rol al primer usuario ──────────────────────
        $adminUser = User::first();
        if ($adminUser) {
            foreach ($guards as $guard) {
                // Le damos super_admin en todos los guards para evitar problemas de acceso en diferentes paneles
                $adminUser->assignRole(Role::where('name', 'super_admin')->where('guard_name', $guard)->first());
            }

            // Si no tiene company_id, asignar la primera empresa
            if (!$adminUser->company_id) {
                $company = Company::first();
                if ($company) {
                    $adminUser->update(['company_id' => $company->id]);
                }
            }
        }
    }
}
