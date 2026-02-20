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
        Schema::table('compra_lineas', function (Blueprint $table) {
            $table->decimal('pvc_dto', 10, 2)->nullable()->after('pvc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('compra_lineas', function (Blueprint $table) {
            $table->dropColumn('pvc_dto');
        });
    }
};
