<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activo_corriente_pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activo_corriente_id')->constrained('activo_corrientes')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users');
            $table->unsignedBigInteger('cierre_caja_id')->nullable();
            $table->decimal('monto', 12, 2);
            $table->timestamp('fecha_pago');
            $table->string('metodo_pago')->default('Efectivo');
            $table->string('referencia')->nullable();
            $table->string('codigo_comprobante')->unique();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        Schema::table('activo_corrientes', function (Blueprint $table) {
            $table->decimal('monto_cobrado', 12, 2)->default(0)->after('monto');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activo_corriente_pagos');
        Schema::table('activo_corrientes', function (Blueprint $table) {
            $table->dropColumn('monto_cobrado');
        });
    }
};
