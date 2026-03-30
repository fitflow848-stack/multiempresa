<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class MigrationProductionSeeder extends Seeder
{
    /**
     * Script para migrar permisos consolidados en producción
     */
    public function run(): void
    {
        echo "=== MIGRANDO PERMISOS A PRODUCCIÓN ===\n\n";
        
        // 1. Crear backup de log
        $backupFile = storage_path('logs/permissions_migration_' . date('Y-m-d_H-i-s') . '.log');
        
        // 2. Eliminar permisos antiguos duplicados
        $permisosAEliminar = [
            'pos.ver', 'pos.crear', 'pos.editar',
            'cajas_registradoras.ver', 'cajas_registradoras.crear', 
            'cajas_registradoras.editar', 'cajas_registradoras.eliminar', 
            'cajas_registradoras.asignar'
        ];
        
        $migrationLog = "MIGRACIÓN DE PERMISOS - " . date('Y-m-d H:i:s') . "\n";
        $migrationLog .= "================================================\n\n";
        
        foreach ($permisosAEliminar as $permisoNombre) {
            $permiso = Permission::where('name', $permisoNombre)->where('guard_name', 'admin')->first();
            if ($permiso) {
                // Log de roles afectados
                $rolesAfectados = $permiso->roles()->pluck('name')->toArray();
                $migrationLog .= "ELIMINAR: $permisoNombre\n";
                $migrationLog .= "  Roles afectados: " . implode(', ', $rolesAfectados) . "\n";
                
                // Eliminar permiso
                $permiso->delete();
                echo "❌ Eliminado: $permisoNombre\n";
            }
        }
        
        // 3. Ejecutar seeder de permisos actualizados
        $this->call(PermissionsSeeder::class);
        echo "✅ Permisos consolidados creados\n";
        
        // 4. Actualizar roles
        $this->call(ExampleRoleSeeder::class);  
        echo "✅ Roles actualizados\n";
        
        // 5. Verificar migración exitosa
        $permisosNuevos = ['ventas.pos', 'equipos_caja.ver', 'equipos_caja.asignar'];
        $migrationLog .= "\nPERMISOS CONSOLIDADOS CREADOS:\n";
        
        foreach ($permisosNuevos as $permiso) {
            $existe = Permission::where('name', $permiso)->where('guard_name', 'admin')->exists();
            $status = $existe ? '✅' : '❌';
            $migrationLog .= "  $status $permiso\n";
            echo "  $status $permiso\n";
        }
        
        // 6. Guardar log
        file_put_contents($backupFile, $migrationLog);
        
        $this->command->info("\n🎉 MIGRACIÓN COMPLETADA");
        $this->command->info("📝 Log guardado en: $backupFile");
        $this->command->info("🔄 Ejecutar: php artisan permission:cache-reset");
    }
}