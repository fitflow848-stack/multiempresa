<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pasivos', function (Blueprint $table) {
            $table->decimal('monto_pagado', 15, 2)->default(0)->after('monto');
            $table->enum('estado', ['pendiente', 'parcial', 'pagado'])->default('pendiente')->after('monto_pagado');
        });
    }

    public function down(): void
    {
        Schema::table('pasivos', function (Blueprint $table) {
            $table->dropColumn(['monto_pagado', 'estado']);
        });
    }
};
