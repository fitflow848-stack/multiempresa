<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banco_movimientos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cuenta_bancaria_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('tipo', ['ingreso', 'egreso']);
            $table->decimal('monto', 12, 2);
            $table->string('concepto');
            $table->string('referencia')->nullable(); // Nro operación, etc.
            $table->date('fecha');
            $table->unsignedBigInteger('cierre_caja_id')->nullable(); // Para vincular con la caja si es pago digital
            $table->unsignedBigInteger('id_venta')->nullable(); // Para vincular con una venta
            $table->timestamps();

            $table->foreign('cuenta_bancaria_id')->references('id')->on('cuenta_bancarias')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banco_movimientos');
    }
};
