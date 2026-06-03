<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banco_movimientos', function (Blueprint $table) {
            if (!Schema::hasColumn('banco_movimientos', 'sucursal_id')) {
                $table->unsignedBigInteger('sucursal_id')->nullable()->after('id_venta');
                $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('banco_movimientos', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn('sucursal_id');
        });
    }
};
