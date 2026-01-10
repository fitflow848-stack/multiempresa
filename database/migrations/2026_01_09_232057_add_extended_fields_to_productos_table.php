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
        Schema::table('productos', function (Blueprint $table) {
            // Características
            $table->json('caracteristicas')->nullable()->after('peso');
            
            // Almacenamiento
            $table->json('almacenamiento')->nullable()->after('caracteristicas');
            
            // Seguridad
            $table->json('seguridad')->nullable()->after('almacenamiento');
            
            // Ficha técnica
            $table->json('ficha_tecnica')->nullable()->after('seguridad');
            
            // Imágenes
            $table->string('imagen_principal')->nullable()->after('ficha_tecnica');
            $table->json('imagenes_adicionales')->nullable()->after('imagen_principal');
            $table->string('imagen_alt')->nullable()->after('imagenes_adicionales');
            $table->string('imagen_titulo')->nullable()->after('imagen_alt');
            $table->string('imagen_fuente')->nullable()->after('imagen_titulo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn([
                'caracteristicas',
                'almacenamiento',
                'seguridad',
                'ficha_tecnica',
                'imagen_principal',
                'imagenes_adicionales',
                'imagen_alt',
                'imagen_titulo',
                'imagen_fuente'
            ]);
        });
    }
};
