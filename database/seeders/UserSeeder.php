<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // NUEVO: Actualizar usuarios existentes sin rol
        User::whereNull('role')->update(['role' => 'user']);
        
        // Crear usuario ADMIN (solo si no existe)
        User::firstOrCreate(
            ['email' => 'admin@ecomarket.test'],
            [
                'name' => 'Admin Academia',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );
        
        // Crear usuario NORMAL (solo si no existe)
        User::firstOrCreate(
            ['email' => 'user@ecomarket.test'],
            [
                'name' => 'Usuario Normal',
                'password' => Hash::make('password'),
                'role' => 'user',
            ]
        );
    }
}
