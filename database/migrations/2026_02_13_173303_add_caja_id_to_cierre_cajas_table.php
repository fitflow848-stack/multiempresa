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
        Schema::table('cierre_cajas', function (Blueprint $table) {
            $table->foreignId('caja_id')->nullable()->after('user_id')
                ->constrained('cajas')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cierre_cajas', function (Blueprint $table) {
            $table->dropForeign(['caja_id']);
            $table->dropColumn('caja_id');
        });
    }
};
