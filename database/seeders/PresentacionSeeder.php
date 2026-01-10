<?php

namespace Database\Seeders;

use App\Models\Presentacion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PresentacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $presentaciones = [
            ['nombre' => 'SACO', 'descripcion' => 'Presentación en saco'],
            ['nombre' => 'BOLSA', 'descripcion' => 'Presentación en bolsa'],
            ['nombre' => 'CAJA', 'descripcion' => 'Presentación en caja'],
            ['nombre' => 'FRASCO', 'descripcion' => 'Presentación en frasco'],
            ['nombre' => 'BLISTER', 'descripcion' => 'Presentación en blister'],
            ['nombre' => 'AMPOLLA', 'descripcion' => 'Presentación en ampolla'],
            ['nombre' => 'VIAL', 'descripcion' => 'Presentación en vial'],
            ['nombre' => 'JERINGA', 'descripcion' => 'Presentación en jeringa'],
            ['nombre' => 'TUBO', 'descripcion' => 'Presentación en tubo'],
            ['nombre' => 'BOTELLA', 'descripcion' => 'Presentación en botella'],
        ];

        foreach ($presentaciones as $presentacion) {
            Presentacion::firstOrCreate(
                ['nombre' => $presentacion['nombre']],
                $presentacion + ['activo' => true]
            );
        }
    }
}
