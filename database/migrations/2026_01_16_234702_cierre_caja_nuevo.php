<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cierre_cajas', function (Blueprint $table) {
            $table->id();
            
            // Relación con usuario
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Datos del arqueo
            $table->timestamp('fecha_cierre')->nullable();
            $table->decimal('monto_apertura', 12, 2)->default(0.00); // Saldo Inicial
            $table->decimal('ingresos', 12, 2)->default(0.00);
            $table->decimal('egresos', 12, 2)->default(0.00);        // Gastos
            $table->decimal('aportaciones', 12, 2)->default(0.00);   // Dinero añadido
            $table->decimal('sustracciones', 12, 2)->default(0.00);  // Dinero retirado
            
            // Resultado final
            $table->decimal('monto_cierre', 12, 2)->default(0.00);   // Efectivo contado
            
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cierre_cajas');
    }
};