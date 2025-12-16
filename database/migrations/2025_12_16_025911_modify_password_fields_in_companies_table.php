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
        Schema::table('companies', function (Blueprint $table) {
            // Cambiar campos de contraseña de string a text para acomodar valores encriptados
            $table->text('sol_password')->nullable()->change();
            $table->text('ose_password')->nullable()->change();
            $table->text('cert_password')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Revertir cambios de text a string
            $table->string('sol_password')->nullable()->change();
            $table->string('ose_password')->nullable()->change();
            $table->string('cert_password')->nullable()->change();
        });
    }
};
