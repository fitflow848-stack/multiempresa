<?php

namespace Database\Seeders;

use App\Models\Concentracion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ConcentracionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $concentraciones = [
            ['nombre' => '1 KG', 'descripcion' => 'Concentración de 1 kilogramo', 'unidad' => 'KG'],
            ['nombre' => '5 KG', 'descripcion' => 'Concentración de 5 kilogramos', 'unidad' => 'KG'],
            ['nombre' => '10 KG', 'descripcion' => 'Concentración de 10 kilogramos', 'unidad' => 'KG'],
            ['nombre' => '20 KG', 'descripcion' => 'Concentración de 20 kilogramos', 'unidad' => 'KG'],
            ['nombre' => '40 KG', 'descripcion' => 'Concentración de 40 kilogramos', 'unidad' => 'KG'],
            ['nombre' => '100 ML', 'descripcion' => 'Concentración de 100 mililitros', 'unidad' => 'ML'],
            ['nombre' => '250 ML', 'descripcion' => 'Concentración de 250 mililitros', 'unidad' => 'ML'],
            ['nombre' => '500 ML', 'descripcion' => 'Concentración de 500 mililitros', 'unidad' => 'ML'],
            ['nombre' => '1000 ML', 'descripcion' => 'Concentración de 1000 mililitros', 'unidad' => 'ML'],
            ['nombre' => '10 MG', 'descripcion' => 'Concentración de 10 miligramos', 'unidad' => 'MG'],
            ['nombre' => '50 MG', 'descripcion' => 'Concentración de 50 miligramos', 'unidad' => 'MG'],
            ['nombre' => '100 MG', 'descripcion' => 'Concentración de 100 miligramos', 'unidad' => 'MG'],
            ['nombre' => '500 MG', 'descripcion' => 'Concentración de 500 miligramos', 'unidad' => 'MG'],
        ];

        foreach ($concentraciones as $concentracion) {
            Concentracion::firstOrCreate(
                ['nombre' => $concentracion['nombre']],
                $concentracion + ['activo' => true]
            );
        }
    }
}
