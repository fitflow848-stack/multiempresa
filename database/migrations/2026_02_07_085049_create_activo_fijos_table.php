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
        Schema::dropIfExists('activos_fijos');
        Schema::create('activos_fijos', function (Blueprint $table) {
            $table->id();
            // Relación con tipo_activos
            $table->foreignId('tipo_activo_id')->constrained('tipo_activos')->onDelete('cascade');

            $table->string('nombre'); // Nombre del activo
            $table->decimal('monto', 15, 2)->default(0); // Valor para el balance
            $table->date('fecha_adquisicion')->useCurrent(); // Fecha y hora (en mockup)
            $table->string('documento')->nullable(); // Documento referencia
            $table->text('observaciones')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activos_fijos');
    }
};
