<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'gestionar usuarios',
            'gestionar roles',
            'gestionar permisos',
            'gestionar proyectos',
            'gestionar campañas',
            'gestionar novedades',
            'crear proyectos',
            'actualizar proyectos',
            'eliminar proyectos',
            'crear campañas',
            'actualizar campañas',
            'eliminar campañas',
            'crear novedades',
            'actualizar novedades',
            'eliminar novedades'
        ];

        $role = Role::where('name', 'super administrador')->first();

        foreach ($permissions as $perm) {
            $permission = Permission::firstOrCreate(['name' => $perm]);
            $role->givePermissionTo($permission);
        }

        $role = Role::where('name', 'administrador')->first();
        $role->givePermissionTo('gestionar proyectos');
        $role->givePermissionTo('gestionar campañas');
        $role->givePermissionTo('gestionar novedades');
        $role->givePermissionTo('crear proyectos');
        $role->givePermissionTo('actualizar proyectos');
        $role->givePermissionTo('eliminar proyectos');
        $role->givePermissionTo('crear campañas');
        $role->givePermissionTo('actualizar campañas');
        $role->givePermissionTo('eliminar campañas');
        $role->givePermissionTo('crear novedades');
        $role->givePermissionTo('actualizar novedades');
        $role->givePermissionTo('eliminar novedades');

        $role = Role::where('name', 'misionero')->first();
        $role->givePermissionTo('gestionar novedades');
        $role->givePermissionTo('crear novedades');
        $role->givePermissionTo('actualizar novedades');
        $role->givePermissionTo('eliminar novedades');
    }
}
