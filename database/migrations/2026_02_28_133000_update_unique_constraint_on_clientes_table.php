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
        Schema::table('clientes', function (Blueprint $table) {
            // Eliminar la restricción única existente
            // El nombre por defecto suele ser: clientes_company_id_numero_documento_tipo_documento_unique
            try {
                $table->dropUnique(['company_id', 'numero_documento', 'tipo_documento']);
            } catch (\Exception $e) {
                // Si ya fue eliminada o tiene otro nombre
            }

            // Nueva restricción única: company_id, sucursal_id, numero_documento
            $table->unique(['company_id', 'sucursal_id', 'numero_documento'], 'clientes_unique_identity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropUnique('clientes_unique_identity');
            $table->unique(['company_id', 'numero_documento', 'tipo_documento']);
        });
    }
};
