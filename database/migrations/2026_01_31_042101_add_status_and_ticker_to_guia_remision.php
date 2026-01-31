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
        Schema::table('guia_remision', function (Blueprint $table) {
            $table->string('ticker')->nullable();
            $table->string('sunat_status')->nullable()->default('PENDIENTE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guia_remision', function (Blueprint $table) {
            $table->dropColumn(['ticker', 'sunat_status']);
        });
    }
};
