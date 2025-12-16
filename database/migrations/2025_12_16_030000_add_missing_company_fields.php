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
            if (! Schema::hasColumn('companies', 'razon_social')) {
                $table->string('razon_social')->nullable();
            }

            if (! Schema::hasColumn('companies', 'nombre_comercial')) {
                $table->string('nombre_comercial')->nullable();
            }

            if (! Schema::hasColumn('companies', 'tipo_contribuyente')) {
                $table->string('tipo_contribuyente')->nullable();
            }

            if (! Schema::hasColumn('companies', 'rep_nombre')) {
                $table->string('rep_nombre')->nullable();
            }

            if (! Schema::hasColumn('companies', 'rep_document_type')) {
                $table->string('rep_document_type')->nullable();
            }

            if (! Schema::hasColumn('companies', 'rep_document_number')) {
                $table->string('rep_document_number')->nullable();
            }

            if (! Schema::hasColumn('companies', 'rep_cargo')) {
                $table->string('rep_cargo')->nullable();
            }

            if (! Schema::hasColumn('companies', 'rep_email')) {
                $table->string('rep_email')->nullable();
            }

            if (! Schema::hasColumn('companies', 'rep_phone')) {
                $table->string('rep_phone')->nullable();
            }

            if (! Schema::hasColumn('companies', 'department')) {
                $table->string('department')->nullable();
            }

            if (! Schema::hasColumn('companies', 'province')) {
                $table->string('province')->nullable();
            }

            if (! Schema::hasColumn('companies', 'district')) {
                $table->string('district')->nullable();
            }

            if (! Schema::hasColumn('companies', 'direccion_fiscal')) {
                $table->text('direccion_fiscal')->nullable();
            }

            if (! Schema::hasColumn('companies', 'ubigeo')) {
                $table->string('ubigeo', 6)->nullable();
            }

            if (! Schema::hasColumn('companies', 'website')) {
                $table->string('website')->nullable();
            }

            if (! Schema::hasColumn('companies', 'regimen')) {
                $table->string('regimen')->nullable();
            }

            if (! Schema::hasColumn('companies', 'afecto_igv')) {
                $table->boolean('afecto_igv')->nullable();
            }

            if (! Schema::hasColumn('companies', 'porcentaje_igv')) {
                $table->decimal('porcentaje_igv', 5, 2)->nullable()->default(18.00);
            }

            if (! Schema::hasColumn('companies', 'sol_user')) {
                $table->string('sol_user')->nullable();
            }

            if (! Schema::hasColumn('companies', 'sol_password')) {
                $table->string('sol_password')->nullable();
            }

            if (! Schema::hasColumn('companies', 'sunat_local_code')) {
                $table->string('sunat_local_code')->nullable();
            }

            if (! Schema::hasColumn('companies', 'ose_provider')) {
                $table->string('ose_provider')->nullable();
            }

            if (! Schema::hasColumn('companies', 'ose_user')) {
                $table->string('ose_user')->nullable();
            }

            if (! Schema::hasColumn('companies', 'ose_password')) {
                $table->string('ose_password')->nullable();
            }

            if (! Schema::hasColumn('companies', 'ose_url')) {
                $table->string('ose_url')->nullable();
            }

            if (! Schema::hasColumn('companies', 'serie_factura')) {
                $table->string('serie_factura')->nullable();
            }

            if (! Schema::hasColumn('companies', 'serie_boleta')) {
                $table->string('serie_boleta')->nullable();
            }

            if (! Schema::hasColumn('companies', 'serie_nota_credito')) {
                $table->string('serie_nota_credito')->nullable();
            }

            if (! Schema::hasColumn('companies', 'serie_nota_debito')) {
                $table->string('serie_nota_debito')->nullable();
            }

            if (! Schema::hasColumn('companies', 'cert_file')) {
                $table->string('cert_file')->nullable();
            }

            if (! Schema::hasColumn('companies', 'cert_password')) {
                $table->string('cert_password')->nullable();
            }

            if (! Schema::hasColumn('companies', 'cert_expires_at')) {
                $table->timestamp('cert_expires_at')->nullable();
            }

            if (! Schema::hasColumn('companies', 'bank')) {
                $table->string('bank')->nullable();
            }

            if (! Schema::hasColumn('companies', 'account_type')) {
                $table->string('account_type')->nullable();
            }

            if (! Schema::hasColumn('companies', 'account_number')) {
                $table->string('account_number')->nullable();
            }

            if (! Schema::hasColumn('companies', 'cci')) {
                $table->string('cci')->nullable();
            }

            if (! Schema::hasColumn('companies', 'fecha_alta')) {
                $table->timestamp('fecha_alta')->nullable();
            }

            if (! Schema::hasColumn('companies', 'observations')) {
                $table->text('observations')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $columns = [
                'razon_social', 'nombre_comercial', 'tipo_contribuyente',
                'rep_nombre', 'rep_document_type', 'rep_document_number', 'rep_cargo', 'rep_email', 'rep_phone',
                'department', 'province', 'district', 'direccion_fiscal', 'ubigeo',
                'website', 'regimen', 'afecto_igv', 'porcentaje_igv',
                'sol_user', 'sol_password', 'sunat_local_code', 'ose_provider', 'ose_user', 'ose_password', 'ose_url',
                'serie_factura', 'serie_boleta', 'serie_nota_credito', 'serie_nota_debito',
                'cert_file', 'cert_password', 'cert_expires_at',
                'bank', 'account_type', 'account_number', 'cci',
                'fecha_alta', 'observations',
            ];

            $existing = array_filter($columns, fn ($col) => Schema::hasColumn('companies', $col));

            if (! empty($existing)) {
                $table->dropColumn($existing);
            }
        });
    }
};
