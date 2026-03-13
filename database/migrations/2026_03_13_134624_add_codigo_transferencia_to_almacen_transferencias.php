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
        Schema::table('almacen_transferencias', function (Blueprint $table) {
            $table->string('codigo_transferencia')->nullable()->after('id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('almacen_transferencias', function (Blueprint $table) {
            $table->dropColumn('codigo_transferencia');
        });
    }
};
