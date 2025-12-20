<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('producto_lineas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();

            $table->string('cb')->nullable(); // código de barras
            $table->string('codigo_ref')->nullable();
            $table->string('presentacion')->nullable();
            $table->string('concentracion')->nullable();
            $table->integer('cantidad')->default(0);
            $table->decimal('precio_compra', 12, 2)->nullable();
            $table->decimal('pvp', 12, 2)->nullable();
            $table->decimal('pvp_dto', 12, 2)->nullable();
            $table->decimal('peso', 12, 3)->nullable();

            // campos adicionales para trazabilidad
            $table->string('pa1')->nullable();
            $table->string('pa2')->nullable();
            $table->string('lote')->nullable();
            $table->date('fecha_venc')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('producto_lineas');
    }
};