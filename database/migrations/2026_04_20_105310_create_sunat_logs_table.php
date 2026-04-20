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
        Schema::create('sunat_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('venta_id')->nullable();
            $table->string('documento_identificador')->nullable(); // Ej: F001-0000001
            $table->string('status'); // 'success', 'error'
            $table->text('message')->nullable();
            $table->json('response_data')->nullable(); // Respuesta completa del API
            $table->string('type')->default('sale'); // 'sale', 'nc', 'guia'
            $table->timestamps();

            $table->index('venta_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sunat_logs');
    }
};
