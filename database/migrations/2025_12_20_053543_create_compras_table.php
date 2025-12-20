<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('proveedor_id')->nullable();
            $table->date('fecha_emision')->nullable();
            $table->date('fecha_pago')->nullable();
            $table->string('moneda')->default('sol');
            $table->boolean('credito')->default(false);
            $table->boolean('percepcion')->default(false);
            $table->boolean('inc_impuesto')->default(false);

            $table->decimal('total_bruto', 14, 2)->default(0);
            $table->decimal('total_descuento', 14, 2)->default(0);
            $table->decimal('bruto_neto', 14, 2)->default(0);
            $table->decimal('total_impuesto', 14, 2)->default(0);
            $table->decimal('total_neto', 14, 2)->default(0);
            $table->decimal('flete', 14, 2)->default(0);
            $table->decimal('total_pagar', 14, 2)->default(0);

            $table->string('tipo')->nullable(); // Ticket/Factura/Boleta
            $table->string('presupuesto')->nullable();
            $table->string('local_destino')->nullable();

            $table->timestamp('received_at')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('compras');
    }
};