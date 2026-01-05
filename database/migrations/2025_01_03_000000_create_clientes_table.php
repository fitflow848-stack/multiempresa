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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->enum('tipo_cliente', ['Particular', 'Empresa'])->default('Particular');
            $table->enum('tipo_documento', ['DNI', 'RUC', 'CE', 'Pasaporte'])->default('DNI');
            $table->string('numero_documento', 20)->index();
            $table->string('nombre');
            $table->text('direccion')->nullable();
            $table->string('distrito', 100)->nullable();
            $table->string('provincia', 100)->nullable();
            $table->string('departamento', 100)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email')->nullable();
            $table->decimal('credito_limite', 10, 2)->default(0);
            $table->boolean('estado')->default(true);
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'numero_documento', 'tipo_documento']);
            $table->index(['company_id', 'estado']);
            $table->index(['company_id', 'nombre']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};