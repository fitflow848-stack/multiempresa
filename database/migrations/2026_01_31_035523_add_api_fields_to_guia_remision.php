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
        Schema::table('guia_remision', function (Blueprint $table) {
            $table->string('serie')->after('id')->nullable();
            $table->string('numero')->after('serie')->nullable();
            $table->string('documento_relacionado')->after('numero')->nullable();
            $table->string('transportista_doc')->nullable();
            $table->string('transportista_nombre')->nullable();
            $table->string('transportista_mtc')->nullable();
            $table->string('motivo_traslado_codigo')->default('01');
            $table->string('modalidad_traslado_codigo')->default('01');
            $table->string('nombre_archivo')->nullable();
            $table->string('hash')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guia_remision', function (Blueprint $table) {
            $table->dropColumn([
                'serie',
                'numero',
                'documento_relacionado',
                'transportista_doc',
                'transportista_nombre',
                'transportista_mtc',
                'motivo_traslado_codigo',
                'modalidad_traslado_codigo',
                'nombre_archivo',
                'hash'
            ]);
        });
    }
};
