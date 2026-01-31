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
        Schema::table('ventas_sunat', function (Blueprint $table) {
            $table->string('cdr_nombre')->nullable()->after('qr_data');
            $table->string('cdr_path')->nullable()->after('cdr_nombre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas_sunat', function (Blueprint $table) {
            $table->dropColumn(['cdr_nombre', 'cdr_path']);
        });
    }
};
