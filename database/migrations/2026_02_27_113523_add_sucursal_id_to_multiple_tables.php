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
        $tables = [
            'clientes' => 'company_id',
            'proveedores' => 'id_empresa',
            'cotizaciones' => 'company_id',
            'pasivos' => 'company_id',
            'operaciones_caja' => 'company_id',
            'cierre_cajas' => 'id_empresa'
        ];
        
        foreach ($tables as $tableName => $afterColumn) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName, $afterColumn) {
                if (!Schema::hasColumn($tableName, 'sucursal_id')) {
                    if (Schema::hasColumn($tableName, $afterColumn)) {
                        $table->unsignedBigInteger('sucursal_id')->nullable()->after($afterColumn);
                    } else {
                        $table->unsignedBigInteger('sucursal_id')->nullable();
                    }
                }
            });
            
            DB::table($tableName)->update(['sucursal_id' => 1]);
        }
    }

    public function down(): void
    {
        $tables = ['clientes', 'proveedores', 'cotizaciones', 'pasivos', 'operaciones_caja', 'cierre_cajas'];
        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                if (Schema::hasColumn($table->getTable(), 'sucursal_id')) {
                    $table->dropColumn('sucursal_id');
                }
            });
        }
    }
};
