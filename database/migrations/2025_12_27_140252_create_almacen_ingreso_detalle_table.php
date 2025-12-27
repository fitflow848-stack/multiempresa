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
        Schema::create('almacen_ingreso_detalle', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ingreso_id');
            $table->unsignedBigInteger('producto_id');
            $table->decimal('cantidad', 10, 2);

            $table->decimal('costo', 10, 2);
            $table->decimal('cop', 10, 2);
            $table->decimal('mu', 5, 2);
            $table->decimal('mud', 5, 2);

            $table->decimal('mup', 10, 2);
            $table->decimal('pvp', 10, 2);
            $table->decimal('pvpd', 10, 2);
            $table->decimal('pvc', 10, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('almacen_ingreso_detalle');
    }
};
