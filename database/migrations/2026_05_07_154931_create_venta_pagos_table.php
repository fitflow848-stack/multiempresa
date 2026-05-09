<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venta_pagos', function (Blueprint $table) {
            $table->id();
            $table->integer('venta_id'); // Match SIGNED int of id_venta in ventas
            $table->unsignedBigInteger('tipo_pago_id');
            $table->decimal('monto', 12, 2);
            $table->string('referencia')->nullable();
            $table->unsignedBigInteger('cuenta_bancaria_id')->nullable();
            $table->timestamps();

            $table->foreign('venta_id')->references('id_venta')->on('ventas')->onDelete('cascade');
            $table->foreign('tipo_pago_id')->references('id')->on('tipos_pagos')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venta_pagos');
    }
};
