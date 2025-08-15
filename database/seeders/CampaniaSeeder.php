<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Campania;

class CampaniaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Campania::create([
            'nombre' => 'Campaña Evangelística',
            'descripcion' => 'Campaña de ejemplo para evangelización',
            'objetivo' => 'Alcanzar a más personas con el mensaje del evangelio',
            'resultado' => 'Se logró un alcance del 150% en la comunidad',
            'fecha_inicio' => '2025-10-01',
            'fecha_fin' => '2025-10-31',
        ]);
        // Puedes agregar más campañas aquí
    }
}
