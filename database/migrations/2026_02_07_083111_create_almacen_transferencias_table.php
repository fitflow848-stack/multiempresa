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
        Schema::create('almacen_transferencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos');
            $table->foreignId('origen_lote_id')->constrained('almacen_ingreso_detalle');
            $table->foreignId('destino_lote_id')->constrained('almacen_ingreso_detalle');
            $table->integer('sucursal_origen_id')->nullable();
            $table->integer('sucursal_destino_id')->nullable();
            $table->decimal('cantidad', 12, 2);
            $table->foreignId('user_id')->constrained('users');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('almacen_transferencias');
    }
};
