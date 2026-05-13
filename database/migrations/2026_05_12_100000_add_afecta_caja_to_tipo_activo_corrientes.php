<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipo_activo_corrientes', function (Blueprint $table) {
            $table->boolean('afecta_caja')->default(true)->after('descripcion');
        });

        // Los tipos "Otros" y cualquier tipo nuevo NO afectan caja
        // Solo los tipos conocidos (Anticipo a Proveedores, Adelantos a Personal, Bancos) afectan caja
        DB::table('tipo_activo_corrientes')
            ->where('nombre', 'Otros')
            ->update(['afecta_caja' => false]);
    }

    public function down(): void
    {
        Schema::table('tipo_activo_corrientes', function (Blueprint $table) {
            $table->dropColumn('afecta_caja');
        });
    }
};
