<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('activo_corrientes', function (Blueprint $table) {
            if (!Schema::hasColumn('activo_corrientes', 'sucursal_id')) {
                $table->foreignId('sucursal_id')->nullable()->after('company_id')->constrained('sucursales');
            }
        });

        Schema::table('activos_fijos', function (Blueprint $table) {
            if (!Schema::hasColumn('activos_fijos', 'sucursal_id')) {
                $table->foreignId('sucursal_id')->nullable()->after('company_id')->constrained('sucursales');
            }
        });
    }

    public function down(): void
    {
        Schema::table('activo_corrientes', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn('sucursal_id');
        });

        Schema::table('activos_fijos', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn('sucursal_id');
        });
    }
};
