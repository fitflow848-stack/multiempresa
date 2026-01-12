<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cierre_cajas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->dateTime('fecha_cierre')->nullable();
            $table->decimal('monto_apertura', 15, 2)->default(0);
            $table->decimal('monto_cierre', 15, 2)->default(0);
            $table->decimal('ingresos', 15, 2)->default(0);
            $table->decimal('egresos', 15, 2)->default(0);
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cierre_cajas');
    }
};
