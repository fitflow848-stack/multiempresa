<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'aprobado' to the estado ENUM
        DB::statement("ALTER TABLE pasivos MODIFY COLUMN estado ENUM('pendiente', 'parcial', 'pagado', 'aprobado') NOT NULL DEFAULT 'pendiente'");

        // Update existing Finanzas records from 'pendiente' to 'aprobado'
        DB::statement("
            UPDATE pasivos p 
            JOIN tipo_pasivos tp ON p.tipo_pasivo_id = tp.id 
            SET p.estado = 'aprobado' 
            WHERE tp.nombre IN ('Compras a credito', 'Adelanto clientes', 'Adelantos personal') 
            AND p.estado = 'pendiente'
        ");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE pasivos MODIFY COLUMN estado ENUM('pendiente', 'parcial', 'pagado') NOT NULL DEFAULT 'pendiente'");
    }
};
