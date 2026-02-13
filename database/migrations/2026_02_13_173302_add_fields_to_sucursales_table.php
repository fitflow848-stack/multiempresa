<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sucursales', function (Blueprint $table) {
            $table->string('logo', 255)->nullable()->after('telefono');
            $table->unsignedInteger('numero_cajas')->default(1)->after('logo');
            $table->boolean('is_active')->default(true)->after('numero_cajas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sucursales', function (Blueprint $table) {
            $table->dropColumn(['logo', 'numero_cajas', 'is_active']);
        });
    }
};
