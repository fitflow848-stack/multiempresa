<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('productos', 'transferencia_gratuita')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->boolean('transferencia_gratuita')->default(false);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('productos', 'transferencia_gratuita')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->dropColumn('transferencia_gratuita');
            });
        }
    }
};
