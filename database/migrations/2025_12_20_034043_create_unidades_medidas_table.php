<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('unidades_medida', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique(); // ej. NIU, KG, LT
            $table->string('nombre'); // ej. "NIU - UNIDAD (BIENES)"
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('unidades_medida');
    }
};