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
        // 1. Corregir tipo_activo_corrientes
        Schema::table('tipo_activo_corrientes', function (Blueprint $table) {
            try {
                $table->dropUnique('tipo_activo_corrientes_nombre_unique');
            } catch (\Exception $e) {
                // Ya fue eliminado o no existe
            }

            try {
                $table->unique(['nombre', 'company_id'], 'tipo_activo_corrientes_nombre_company_unique');
            } catch (\Exception $e) {
                // Ya existe
            }
        });

        // 2. Corregir tipo_activos
        Schema::table('tipo_activos', function (Blueprint $table) {
            try {
                $table->dropUnique('tipo_activos_nombre_unique');
            } catch (\Exception $e) {
                // Ya fue eliminado o no existe
            }

            try {
                $table->unique(['nombre', 'company_id'], 'tipo_activos_nombre_company_unique');
            } catch (\Exception $e) {
                // Ya existe
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tipo_activo_corrientes', function (Blueprint $table) {
            try {
                $table->dropUnique('tipo_activo_corrientes_nombre_company_unique');
                $table->unique('nombre', 'tipo_activo_corrientes_nombre_unique');
            } catch (\Exception $e) {}
        });

        Schema::table('tipo_activos', function (Blueprint $table) {
            try {
                $table->dropUnique('tipo_activos_nombre_company_unique');
                $table->unique('nombre', 'tipo_activos_nombre_unique');
            } catch (\Exception $e) {}
        });
    }
};
