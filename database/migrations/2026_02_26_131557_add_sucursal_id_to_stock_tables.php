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
        Schema::table('almacen_ingresos', function (Blueprint $table) {
            if (!Schema::hasColumn('almacen_ingresos', 'sucursal_id')) {
                $table->unsignedBigInteger('sucursal_id')->nullable()->after('user_id');
            }
        });

        // Set sucursal_id based on user's branch
        DB::statement('UPDATE almacen_ingresos i JOIN users u ON i.user_id = u.id SET i.sucursal_id = u.branch_id WHERE i.sucursal_id IS NULL AND u.branch_id IS NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('almacen_ingresos', function (Blueprint $table) {
            if (Schema::hasColumn('almacen_ingresos', 'sucursal_id')) {
                $table->dropColumn('sucursal_id');
            }
        });
    }
};
