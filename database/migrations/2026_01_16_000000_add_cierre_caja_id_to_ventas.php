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
        Schema::table('ventas', function (Blueprint $table) {
            $table->unsignedBigInteger('cierre_caja_id')->nullable()->after('id_empresa');
            $table->foreign('cierre_caja_id')->references('id')->on('cierre_cajas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            if (Schema::hasColumn('ventas', 'cierre_caja_id')) {
                $table->dropForeign(['cierre_caja_id']);
                $table->dropColumn('cierre_caja_id');
            }
        });
    }
};
