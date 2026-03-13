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
            $table->string('vehiculo_placa')->nullable()->after('transportista_mtc');
            $table->string('conductor_doc_tipo')->nullable()->after('vehiculo_placa');
            $table->string('conductor_doc_numero')->nullable()->after('conductor_doc_tipo');
            $table->string('conductor_nombre')->nullable()->after('conductor_doc_numero');
            $table->string('conductor_licencia')->nullable()->after('conductor_nombre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guia_remision', function (Blueprint $table) {
            $table->dropColumn([
                'vehiculo_placa',
                'conductor_doc_tipo',
                'conductor_doc_numero',
                'conductor_nombre',
                'conductor_licencia'
            ]);
        });
    }
};
