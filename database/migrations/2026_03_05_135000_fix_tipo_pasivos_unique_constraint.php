<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipo_pasivos', function (Blueprint $table) {
            // Eliminar el unique constraint global en nombre
            try {
                $table->dropUnique('tipo_pasivos_nombre_unique');
            } catch (\Exception $e) {
                // Ya fue eliminado o no existe
            }

            // Crear unique compuesto (nombre, company_id) — permite mismo nombre en distintas empresas
            // company_id nullable (tipos de sistema sin empresa)
            try {
                $table->unique(['nombre', 'company_id'], 'tipo_pasivos_nombre_company_unique');
            } catch (\Exception $e) {
                // Ya existe
            }
        });
    }

    public function down(): void
    {
        Schema::table('tipo_pasivos', function (Blueprint $table) {
            try {
                $table->dropUnique('tipo_pasivos_nombre_company_unique');
            } catch (\Exception $e) {}

            try {
                $table->unique('nombre', 'tipo_pasivos_nombre_unique');
            } catch (\Exception $e) {}
        });
    }
};
