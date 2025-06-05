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
        $roles = [
            ['name' => 'Admin', 'descripcion' => 'Administrador del sistema con todos los permisos', 'guard_name' => 'sanctum'],
            ['name' => 'Digitador', 'descripcion' => 'Encargado de digitalizar libros', 'guard_name' => 'sanctum'],

        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(['name' => $roleData['name'], 'guard_name' => $roleData['guard_name']], $roleData);
        }
    }
}
