<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener los roles o crear si no existen
        $roleAdmin = Role::firstOrCreate(
            ['name' => 'Admin', 'guard_name' => 'sanctum']
        );
        $roleDigitador = Role::firstOrCreate(
            ['name' => 'Digitador', 'guard_name' => 'sanctum']
        );


        // Crear usuario Super administrador (no está ligado a ningún empleado)
        User::create([
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
        ])->assignRole($roleAdmin);

        User::create([
            'username' => 'digitador',
            'email' => 'digitador@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
        ])->assignRole($roleDigitador);



    }
}
