<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::firstOrCreate([
            'email' => 'ivanfischer76@gmail.com'
        ], [
            'username' => 'ivan.fischer',
            'apellido' => 'Fischer',
            'nombre' => 'Iván Gustavo',
            'iglesia' => 'Asociación Iglesia del Señor',
            'password' => Hash::make('Wsxdr5tgbhu'),
        ]);
        $user->assignRole('super administrador');
        // Puedes agregar más usuarios aquí
    }
}
