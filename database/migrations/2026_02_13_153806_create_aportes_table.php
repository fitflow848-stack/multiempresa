<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('aportes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_aporte_id')->constrained('tipo_aportes');
            $table->string('nombre');
            $table->decimal('monto', 15, 2);
            $table->date('fecha_registro');
            $table->string('documento')->nullable();
            $table->text('observaciones')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aportes');
    }
};
