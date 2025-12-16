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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            // Identificación fiscal
            $table->string('ruc', 11)->unique();
            $table->string('razon_social');
            $table->string('nombre_comercial')->nullable();
            $table->string('tipo_contribuyente')->nullable(); // e.g. Persona Natural con Negocio, Persona Jurídica

            // Representante legal
            $table->string('rep_nombre')->nullable();
            $table->string('rep_document_type')->nullable(); // DNI, CE, etc.
            $table->string('rep_document_number')->nullable();
            $table->string('rep_cargo')->nullable();
            $table->string('rep_email')->nullable();
            $table->string('rep_phone')->nullable();

            // Ubicación fiscal
            $table->string('department')->nullable();
            $table->string('province')->nullable();
            $table->string('district')->nullable();
            $table->text('direccion_fiscal')->nullable();
            $table->string('ubigeo', 6)->nullable();

            // Datos de contacto
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();

            // Régimen tributario
            $table->string('regimen')->nullable();
            $table->boolean('afecto_igv')->nullable();
            $table->decimal('porcentaje_igv', 5, 2)->nullable()->default(18.00);

            // Facturación electrónica
            $table->string('sol_user')->nullable();
            $table->string('sol_password')->nullable();
            $table->string('sunat_local_code')->nullable();

            // Proveedor OSE/PSE
            $table->string('ose_provider')->nullable();
            $table->string('ose_user')->nullable();
            $table->string('ose_password')->nullable();
            $table->string('ose_url')->nullable();

            // Series de comprobantes
            $table->string('serie_factura')->nullable();
            $table->string('serie_boleta')->nullable();
            $table->string('serie_nota_credito')->nullable();
            $table->string('serie_nota_debito')->nullable();

            // Certificado digital
            $table->string('cert_file')->nullable(); // path or identifier
            $table->string('cert_password')->nullable();
            $table->timestamp('cert_expires_at')->nullable();

            // Información bancaria
            $table->string('bank')->nullable();
            $table->string('account_type')->nullable();
            $table->string('account_number')->nullable();
            $table->string('cci')->nullable();

            // Estado y control interno
            $table->boolean('is_active')->default(true);
            $table->timestamp('fecha_alta')->nullable();
            $table->text('observations')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
