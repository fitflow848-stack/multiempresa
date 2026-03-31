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
            // Usuarios y sistema
            'usuarios.ver',          // Puede ver usuarios
            
            // Productos e inventario
            'productos.ver',         // Puede ver productos
            'productos.crear',       // Puede crear productos
            'productos.editar',      // Puede editar productos
            'inventario.ver',        // Puede ver inventario
            'inventario.kardex',     // Puede ver kardex/movimientos
            'inventario.transferir', // Puede realizar transferencias
            
            // Clientes y operaciones comerciales
            'clientes.ver',          // Puede ver clientes
            'clientes.crear',        // Puede crear clientes
            'clientes.editar',       // Puede editar clientes
            'cotizaciones.ver',      // Puede ver cotizaciones
            'cotizaciones.crear',    // Puede crear cotizaciones
            'cotizaciones.convertir', // Puede convertir cotizaciones
            
            // Ventas y POS
            'ventas.ver',            // Puede ver ventas
            'ventas.crear',          // Puede realizar ventas
            'ventas.pos',            // Puede usar POS
            
            // Comprobantes
            'comprobantes.ver',      // Puede ver comprobantes
            'comprobantes.imprimir', // Puede imprimir comprobantes
            
            // Caja y equipos
            'cajas.ver',             // Puede ver cajas
            'cajas.abrir_cerrar',    // Puede abrir/cerrar cajas
            'equipos_caja.ver',      // Puede ver equipos de caja
            'tesoreria.ver',         // Puede ver tesorería (solo lectura)
            
            // Reportes
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
            // Clientes
            'clientes.ver',          // Puede ver clientes
            'clientes.crear',        // Puede crear clientes
            
            // Ventas y POS
            'ventas.ver',            // Puede ver ventas
            'ventas.crear',          // Puede realizar ventas
            'ventas.pos',            // Puede usar POS
            
            // Productos (consulta)
            'productos.ver',         // Puede consultar productos
            
            // Comprobantes
            'comprobantes.ver',      // Puede ver comprobantes
            'comprobantes.imprimir', // Puede imprimir comprobantes
            
            // Operaciones de caja específicas
            'cajas.ver',             // Puede ver cajas
            'cajas.abrir_cerrar',    // Puede abrir/cerrar cajas
            'cajas.ajustar',         // Puede hacer ajustes de caja
            'cajas.arquear',         // Puede realizar arqueos
            'equipos_caja.ver',      // Puede ver equipos de caja
            'equipos_caja.asignar',  // Puede asignarse equipos
            
            // Tesorería básica
            'tesoreria.ver',         // Puede ver tesorería básica
            
            // Reportes básicos de caja
            'reportes.ver',          // Puede ver reportes de caja
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
            // Finanzas completas
            'finanzas.ver',          // Puede ver finanzas
            'finanzas.balance',      // Puede ver balance
            'finanzas.estado_resultados', // Puede ver estado de resultados
            
            // Contabilidad
            'contabilidad.ver',              // Puede ver balance general
            'contabilidad.gestionar_activos', // Puede gestionar activos
            'contabilidad.gestionar_pasivos', // Puede gestionar pasivos
            
            // Deudas y pagos
            'deudas.ver',            // Puede ver deudas
            'deudas.pagar',          // Puede registrar pagos
            'deudas.reporte',        // Puede generar reportes de deudas
            
            // Tesorería y caja
            'tesoreria.ver',         // Puede ver tesorería
            'tesoreria.crear',       // Puede crear movimientos de tesorería
            'cajas.ver',             // Puede ver cajas
            
            // Compras y proveedores
            'compras.ver',           // Puede ver compras
            'compras.crear',         // Puede registrar compras
            'compras.recibir',       // Puede recibir productos de compras
            'proveedores.ver',       // Puede ver proveedores
            'proveedores.crear',     // Puede crear proveedores
            
            // Inventario
            'inventario.ver',        // Puede ver inventario
            'inventario.kardex',     // Puede ver kardex/movimientos
            'productos.ver',         // Puede ver productos
            
            // Comprobantes
            'comprobantes.ver',      // Puede ver comprobantes
            'comprobantes.imprimir', // Puede imprimir comprobantes
            'comprobantes.cancelar', // Puede cancelar comprobantes
            'comprobantes.anular',   // Puede anular comprobantes
            
            // Reportes completos
            'reportes.ver',          // Puede ver reportes
            'reportes.crear',        // Puede generar reportes
            'reportes.exportar',     // Puede exportar reportes
            
            // Guías de remisión
            'guias_remision.ver',    // Puede ver guías
            'guias_remision.crear',  // Puede crear guías
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