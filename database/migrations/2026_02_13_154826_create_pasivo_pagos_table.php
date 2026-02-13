<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pasivo_pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pasivo_id')->constrained('pasivos')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users');
            $table->decimal('monto', 15, 2);
            $table->date('fecha_pago');
            $table->string('metodo_pago'); // Efectivo, Transferencia, etc.
            $table->string('documento_pago')->nullable(); // Nro Operación
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pasivo_pagos');
    }
};
