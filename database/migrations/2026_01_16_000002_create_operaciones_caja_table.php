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
        Schema::create('operaciones_caja', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cierre_caja_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('tipo'); // aportacion|sustraccion|ingreso|gasto
            $table->string('partida')->nullable();
            $table->text('concepto')->nullable();
            $table->decimal('importe', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('cierre_caja_id')->references('id')->on('cierre_cajas')->onDelete('set null');
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
        Schema::dropIfExists('operaciones_caja');
    }
};
