<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('subfamilias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('familia_id')->constrained('familias')->onDelete('cascade');
            $table->string('nombre');
            $table->timestamps();

            $table->unique(['familia_id','nombre']); // evita duplicados por familia+nombre
        });
    }

    public function down()
    {
        Schema::dropIfExists('subfamilias');
    }
};