<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cierre_cajas', function (Blueprint $table) {
            if (!Schema::hasColumn('cierre_cajas', 'estado')) {
                $table->string('estado')->default('abierta')->after('fecha_cierre');
            }
        });
    }

    public function down()
    {
        Schema::table('cierre_cajas', function (Blueprint $table) {
            $table->dropColumn('estado');
        });
    }
};
