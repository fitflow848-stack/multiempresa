<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tipo_activo_corrientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->string('descripcion')->nullable();
            $table->timestamps();
        });

        // Seed default types
        DB::table('tipo_activo_corrientes')->insert([
            ['nombre' => 'Bancos', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Cuentas por Cobrar (CxC)', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Anticipo a Proveedores', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Adelantos a Personal', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Otros', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_activo_corrientes');
    }
};
