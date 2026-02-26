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
            'compras', 'deudas', 'arqueo_cajas', 'familias', 'laboratorios', 
            'marcas', 'unidades_medida', 'presentaciones', 'concentraciones', 
            'pasivos', 'activo_corrientes', 'activos_fijos', 'aportes',
            'tipo_activos', 'tipo_pasivos', 'tipo_activo_corrientes', 'tipo_aportes',
            'operaciones_caja', 'almacen_ingresos'
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'company_id') && !Schema::hasColumn($tableName, 'id_empresa')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->unsignedBigInteger('company_id')->nullable()->after('id');
                    $table->index('company_id');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'compras', 'deudas', 'arqueo_cajas', 'familias', 'laboratorios', 
            'marcas', 'unidades_medida', 'presentaciones', 'concentraciones', 
            'pasivos', 'activo_corrientes', 'activos_fijos', 'aportes',
            'tipo_activos', 'tipo_pasivos', 'tipo_activo_corrientes', 'tipo_aportes',
            'operaciones_caja', 'almacen_ingresos'
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'company_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('company_id');
                });
            }
        }
    }
};
