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
        Schema::table('laboratorios', function (Blueprint $table) {
            // Eliminar el índice único antiguo si existe
            $table->dropUnique(['nombre']);
            
            // Crear el nuevo índice único compuesto por nombre y empresa
            $table->unique(['nombre', 'company_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laboratorios', function (Blueprint $table) {
            $table->dropUnique(['nombre', 'company_id']);
            $table->unique('nombre');
        });
    }
};
