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
        Schema::table('deuda_pagos', function (Blueprint $table) {
            $table->unsignedBigInteger('cierre_caja_id')->nullable()->after('user_id');
            $table->unsignedBigInteger('caja_id')->nullable()->after('cierre_caja_id');
            
            $table->foreign('cierre_caja_id')->references('id')->on('cierre_cajas')->onDelete('set null');
            $table->foreign('caja_id')->references('id')->on('cajas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deuda_pagos', function (Blueprint $table) {
            $table->dropForeign(['cierre_caja_id']);
            $table->dropForeign(['caja_id']);
            $table->dropColumn(['cierre_caja_id', 'caja_id']);
        });
    }
};
