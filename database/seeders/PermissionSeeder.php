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
            'actualizar usuarios',
            'actualizar campañas',
            'actualizar novedades',
            'actualizar proyectos',
            'blanquear password',
            'crear campañas',
            'crear novedades',
            'crear proyectos',
            'crear usuarios',
            'editar usuarios',
            'eliminar campañas',
            'eliminar novedades',
            'eliminar proyectos',
            'eliminar usuarios',
            'gestionar campañas',
            'gestionar bienvenida',
            'gestionar novedades',
            'gestionar permisos',
            'gestionar proyectos',
            'gestionar roles',
            'gestionar contactos',
            'gestionar sistema',
            'gestionar usuarios',
            'ver estadísticas',
        ];

        $role = Role::where('name', 'super administrador')
            ->where('guard_name', 'api')
            ->first();

        foreach ($permissions as $perm) {
            $permission = Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'api']);
            $role->givePermissionTo($permission);
        }

        $role = Role::where('name', 'administrador')
            ->where('guard_name', 'api')
            ->first();
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

        $role = Role::where('name', 'misionero')
            ->where('guard_name', 'api')
            ->first();
        $role->givePermissionTo('gestionar novedades');
        $role->givePermissionTo('crear novedades');
        $role->givePermissionTo('actualizar novedades');
        $role->givePermissionTo('eliminar novedades');
    }
}
