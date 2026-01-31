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
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['serie_factura', 'serie_boleta', 'serie_nota_credito', 'serie_nota_debito']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('serie_factura')->nullable();
            $table->string('serie_boleta')->nullable();
            $table->string('serie_nota_credito')->nullable();
            $table->string('serie_nota_debito')->nullable();
        });
    }
};
