<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_empresa');
            
            $table->string('nombre');
            $table->string('laboratorio')->nullable();

            $table->foreignId('familia_id')->nullable()->constrained('familias')->nullOnDelete();
            $table->foreignId('subfamilia_id')->nullable()->constrained('subfamilias')->nullOnDelete();
            $table->foreignId('marca_id')->nullable()->constrained('marcas')->nullOnDelete();
            $table->foreignId('unidad_medida_id')->nullable()->constrained('unidades_medida')->nullOnDelete();

            $table->string('tipo_impuesto')->nullable();
            $table->boolean('opciones_avanzadas')->default(false);
            $table->string('condicion_venta')->nullable();

            $table->boolean('attr_numero_serie')->default(false);
            $table->boolean('attr_fecha_vencimiento')->default(false);
            $table->boolean('attr_lote_produccion')->default(false);
            $table->boolean('attr_venta_menudeo')->default(false);

            // Campos adicionales del formulario detallado
            $table->string('codigo_barras')->nullable();
            $table->string('registro_sanitario')->nullable();
            $table->string('presentacion_modelo')->nullable();
            $table->string('concentracion_detalle')->nullable();
            $table->integer('cantidad')->nullable()->default(0);

            $table->decimal('precio_compra', 12, 2)->nullable();
            $table->decimal('pvp', 12, 2)->nullable();
            $table->decimal('pv_docena', 12, 2)->nullable();
            $table->decimal('pvp_dto', 12, 2)->nullable();
            $table->decimal('pvc', 12, 2)->nullable();
            $table->decimal('pvc_dto', 12, 2)->nullable();
            $table->decimal('costo_operativo', 12, 2)->nullable()->default(0);
            $table->decimal('peso', 12, 3)->nullable()->default(0);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('productos');
    }
};