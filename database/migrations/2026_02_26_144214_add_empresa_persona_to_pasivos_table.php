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
        Schema::table('pasivos', function (Blueprint $table) {
            $table->string('empresa_persona')->nullable()->after('nombre');
        });
    }

    public function down(): void
    {
        Schema::table('pasivos', function (Blueprint $table) {
            $table->dropColumn('empresa_persona');
        });
    }
};
