<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        // Role::firstOrCreate(['name' => 'super administrador', 'guard_name' => 'web']);
        // Role::firstOrCreate(['name' => 'super administrador', 'guard_name' => 'web']);
        // Role::firstOrCreate(['name' => 'administrador', 'guard_name' => 'web']);
        // Role::firstOrCreate(['name' => 'usuario', 'guard_name' => 'web']);
        // Role::firstOrCreate(['name' => 'misionero', 'guard_name' => 'web']);
        // Role::firstOrCreate(['name' => 'enviador', 'guard_name' => 'web']);
        
        Role::firstOrCreate(['name' => 'super administrador', 'guard_name' => 'api']);
        Role::firstOrCreate(['name' => 'super administrador', 'guard_name' => 'api']);
        Role::firstOrCreate(['name' => 'administrador', 'guard_name' => 'api']);
        Role::firstOrCreate(['name' => 'usuario', 'guard_name' => 'api']);
        Role::firstOrCreate(['name' => 'misionero', 'guard_name' => 'api']);
        Role::firstOrCreate(['name' => 'enviador', 'guard_name' => 'api']);
    }
}
