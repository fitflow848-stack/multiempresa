<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Insertar permiso si no existe
        $exists = DB::table('permissions')->where('name', 'bancos.ver')->exists();
        if (!$exists) {
            DB::table('permissions')->insert([
                'name' => 'bancos.ver',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Asignar a roles admin
        $permiso = DB::table('permissions')->where('name', 'bancos.ver')->first();
        if ($permiso) {
            $roles = DB::table('roles')->whereIn('name', ['super_admin', 'admin_empresa', 'admin', 'administrador'])->pluck('id');
            foreach ($roles as $roleId) {
                $exists = DB::table('role_has_permissions')
                    ->where('permission_id', $permiso->id)
                    ->where('role_id', $roleId)
                    ->exists();
                if (!$exists) {
                    DB::table('role_has_permissions')->insert([
                        'permission_id' => $permiso->id,
                        'role_id' => $roleId,
                    ]);
                }
            }
        }

        // Registrar reporte de movimientos de banco
        $existsReport = DB::table('reports')->where('method', 'reporteMovimientosBanco')->exists();
        if (!$existsReport) {
            // Obtener la categoría de finanzas/caja
            $categoria = DB::table('report_categories')->where('name', 'like', '%Caja%')->first();
            $catId = $categoria ? $categoria->id : DB::table('report_categories')->insertGetId([
                'name' => 'Caja y Bancos',
                'icon' => 'bx-money',
                'order' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('reports')->insert([
                'name' => 'Movimientos de Banco',
                'description' => 'Historial de ingresos y egresos en cuentas bancarias',
                'method' => 'reporteMovimientosBanco',
                'report_category_id' => $catId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $permiso = DB::table('permissions')->where('name', 'bancos.ver')->first();
        if ($permiso) {
            DB::table('role_has_permissions')->where('permission_id', $permiso->id)->delete();
            DB::table('permissions')->where('id', $permiso->id)->delete();
        }
    }
};
