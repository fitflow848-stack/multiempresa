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
        Schema::create('tipo_pasivos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->string('descripcion')->nullable();
            $table->timestamps();
        });

        // Insert default types
        DB::table('tipo_pasivos')->insert([
            ['nombre' => 'Compras a crédito', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Adelanto de clientes', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Deuda de bancos', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Cxp terceros', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Aporte', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Beneficio', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Impuestos o renta', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Otros', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_pasivos');
    }
};
