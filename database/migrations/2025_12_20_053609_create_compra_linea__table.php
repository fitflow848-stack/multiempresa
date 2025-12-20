<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('compra_lineas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compra_id')->constrained('compras')->cascadeOnDelete();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('cb')->nullable();
            $table->text('descripcion')->nullable();
            $table->integer('cantidad')->default(0);
            $table->decimal('costo', 14, 2)->nullable();
            $table->decimal('descuento', 14, 2)->nullable();
            $table->string('vcpc')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('compra_lineas');
    }
};