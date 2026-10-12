<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!DB::table('reports')->where('method', 'reporteUtilidadBruta')->exists()) {
            DB::table('reports')->insert([
                'category' => 'ventas',
                'name' => 'RESUMEN DE UTILIDAD',
                'method' => 'reporteUtilidadBruta',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('reports')->where('method', 'reporteUtilidadBruta')->delete();
    }
};
