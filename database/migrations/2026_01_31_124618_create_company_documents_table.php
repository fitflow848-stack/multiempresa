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
        Schema::create('company_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('sucursales')->onDelete('cascade');
            // Assuming 'documentos_sunat' exists and its primary key is compatible. 
            // If the user's table name or PK differs, this might need adjustment.
            // Based on user request "id de la tabla documnetos_sunat", and image showing `id` as `int(11)`.
            // We'll use a foreign key if possible, or just an integer column if we want to be safe initially.
            // Let's try to constrain it.
            $table->unsignedInteger('sunat_document_id');
            // Note: If documentos_sunat uses bigInteger, this should be foreignId or unsignedBigInteger.
            // Image showed int(11), so unsignedInteger matches standard int.

            $table->string('series');
            $table->integer('number')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_documents');
    }
};
