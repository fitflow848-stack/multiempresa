<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deuda_pagos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('deuda_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->decimal('monto', 10, 2);
            $table->dateTime('fecha_pago');
            $table->string('metodo_pago')->default('Efectivo');
            $table->string('referencia')->nullable();
            $table->string('codigo_comprobante')->nullable()->unique();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->foreign('deuda_id')->references('id')->on('deudas')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deuda_pagos');
    }
};
