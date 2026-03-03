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
        Schema::table('activo_corrientes', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->foreignId('cierre_caja_id')->nullable()->constrained('cierre_cajas');
            $table->foreignId('id_operacion_caja')->nullable()->constrained('operaciones_caja');
            $table->boolean('is_settled')->default(false)->after('observaciones');
            $table->string('tipo_adelanto')->nullable()->after('is_settled'); // 'personal', 'cliente', etc
        });

        Schema::table('pasivos', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->foreignId('cierre_caja_id')->nullable()->constrained('cierre_cajas');
            $table->foreignId('id_operacion_caja')->nullable()->constrained('operaciones_caja');
            $table->boolean('is_settled')->default(false)->after('observaciones');
            $table->string('tipo_adelanto')->nullable()->after('is_settled'); // 'personal', 'cliente', etc
            $table->boolean('is_compra_credito')->default(false)->after('tipo_adelanto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activo_corrientes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropConstrainedForeignId('cierre_caja_id');
            $table->dropConstrainedForeignId('id_operacion_caja');
            $table->dropColumn(['is_settled', 'tipo_adelanto']);
        });

        Schema::table('pasivos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropConstrainedForeignId('cierre_caja_id');
            $table->dropConstrainedForeignId('id_operacion_caja');
            $table->dropColumn(['is_settled', 'tipo_adelanto', 'is_compra_credito']);
        });
    }
};
