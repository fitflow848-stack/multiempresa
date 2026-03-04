<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Corregir FAMILIAS
        $this->safeDropUnique('familias', 'familias_nombre_unique');
        $this->safeAddUnique('familias', ['nombre', 'company_id']);

        // Corregir MARCAS
        $this->safeDropUnique('marcas', 'marcas_nombre_unique');
        $this->safeAddUnique('marcas', ['nombre', 'company_id']);

        // Corregir UNIDADES_MEDIDA
        $this->safeDropUnique('unidades_medida', 'unidades_medida_codigo_unique');
        $this->safeAddUnique('unidades_medida', ['codigo', 'company_id']);

        // Corregir PRESENTACIONES
        $this->safeDropUnique('presentaciones', 'presentaciones_nombre_unique');
        $this->safeAddUnique('presentaciones', ['nombre', 'company_id']);

        // Corregir CONCENTRACIONES
        $this->safeDropUnique('concentraciones', 'concentraciones_nombre_unique');
        $this->safeAddUnique('concentraciones', ['nombre', 'company_id']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('familias', function (Blueprint $table) {
            $table->dropUnique(['nombre', 'company_id']);
            $table->unique('nombre');
        });
    }

    /**
     * Helper para eliminar un índice único de forma segura
     */
    private function safeDropUnique(string $tableName, string $indexName): void
    {
        try {
            Schema::table($tableName, function (Blueprint $table) use ($indexName) {
                $table->dropUnique($indexName);
            });
        } catch (\Exception $e) {
            // Ignorar si no existe
        }
    }

    /**
     * Helper para añadir un índice único de forma segura
     */
    private function safeAddUnique(string $tableName, array $columns): void
    {
        try {
            Schema::table($tableName, function (Blueprint $table) use ($columns) {
                $table->unique($columns);
            });
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'Duplicate key name')) {
                return; // Ya existe
            }
            throw $e;
        }
    }
};
