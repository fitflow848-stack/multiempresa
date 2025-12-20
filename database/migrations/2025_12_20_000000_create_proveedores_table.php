
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProveedoresTable extends Migration
{
    public function up()
    {
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_empresa');
            $table->string('ruc', 11)->nullable()->unique();
            $table->string('nombre_comercial')->nullable();
            $table->string('nombre_legal')->nullable();
            $table->text('direccion')->nullable();
            $table->string('localidad')->nullable();
            $table->string('codigo_postal')->nullable();
            $table->string('ubigeo')->nullable();
            $table->string('email')->nullable();
            $table->string('telefono')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('proveedores');
    }
}