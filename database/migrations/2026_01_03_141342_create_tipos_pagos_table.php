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
        Schema::create('tipos_pagos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100); // Efectivo, Yape, Plin, etc.
            $table->string('codigo', 20)->unique(); // EFE, YAP, PLN, etc.
            $table->text('descripcion')->nullable();
            $table->string('icono', 50)->nullable(); // Para mostrar iconos en el frontend
            $table->string('color', 7)->nullable(); // Color hex para el frontend
            $table->boolean('activo')->default(true);
            $table->boolean('requiere_referencia')->default(false); // Si necesita número de operación
            $table->boolean('es_digital')->default(false); // Yape, Plin, transferencias
            $table->boolean('es_efectivo')->default(false); // Solo efectivo
            $table->decimal('comision_porcentaje', 5, 2)->nullable(); // Comisión que cobra el método
            $table->decimal('comision_fija', 10, 2)->nullable(); // Comisión fija
            $table->integer('orden')->default(0); // Para ordenar en la interfaz
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipos_pagos');
    }
};
