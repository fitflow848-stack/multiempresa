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
        Schema::dropIfExists('pasivos');

        Schema::create('pasivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_pasivo_id')->constrained('tipo_pasivos');
            $table->string('nombre');
            $table->decimal('monto', 12, 2);
            $table->date('fecha_registro');
            $table->string('documento')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pasivos');
    }
};
