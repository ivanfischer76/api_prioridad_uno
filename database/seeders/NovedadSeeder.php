<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Novedad;

class NovedadSeeder extends Seeder
{
    public function run(): void
    {
        Novedad::create([
            'titulo' => 'Inicio de proyecto',
            'descripcion' => 'El proyecto ha comenzado oficialmente.',
            'motivos_oracion' => 'Orar por la unidad del equipo y la claridad en los objetivos.',
            'fecha' => '2025-09-01',
            'proyecto_id' => 1,
        ]);
        Novedad::create([
            'titulo' => 'Primera reunión',
            'descripcion' => 'Se realizó la primera reunión de coordinación.',
            'motivos_oracion' => null,
            'fecha' => '2025-09-10',
            'proyecto_id' => 1,
        ]);
    }
}
