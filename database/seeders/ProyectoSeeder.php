<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Proyecto;

class ProyectoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Proyecto::create([
            'nombre' => 'Amazonas Boliviano',
            'descripcion' => 'Evangelización de las etnias en el amazonas boliviano',
            'fecha_inicio' => '2025-09-01',
            'fecha_fin' => null,
        ]);
        // Puedes agregar más proyectos aquí
    }
}
