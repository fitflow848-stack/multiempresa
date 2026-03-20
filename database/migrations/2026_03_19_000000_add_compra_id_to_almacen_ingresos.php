<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('almacen_ingresos', function (Blueprint $table) {
            $table->unsignedBigInteger('compra_id')->nullable()->after('user_id');
            $table->foreign('compra_id')->references('id')->on('compras')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('almacen_ingresos', function (Blueprint $table) {
            $table->dropForeign(['compra_id']);
            $table->dropColumn('compra_id');
        });
    }
};
