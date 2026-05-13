<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activos_fijos', function (Blueprint $table) {
            $table->boolean('is_paid')->default(false)->after('observaciones');
            $table->string('metodo_pago')->nullable()->after('is_paid');
            $table->unsignedBigInteger('cierre_caja_id')->nullable()->after('metodo_pago');
        });
    }

    public function down(): void
    {
        Schema::table('activos_fijos', function (Blueprint $table) {
            $table->dropColumn(['is_paid', 'metodo_pago', 'cierre_caja_id']);
        });
    }
};
