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
        Schema::table('pasivos', function (Blueprint $table) {
            $table->string('metodo_pago')->nullable()->after('monto');
        });
        Schema::table('activo_corrientes', function (Blueprint $table) {
            $table->string('metodo_pago')->nullable()->after('monto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pasivos', function (Blueprint $table) {
            $table->dropColumn('metodo_pago');
        });
        Schema::table('activo_corrientes', function (Blueprint $table) {
            $table->dropColumn('metodo_pago');
        });
    }
};
