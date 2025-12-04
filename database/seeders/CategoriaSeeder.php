<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Categoria;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        Categoria::create([
            'nombre' => 'IA',
            'descripcion' => 'Actualiza tu chip a la nueva era de la IA'
        ]);

        Categoria::create([
            'nombre' => 'Hostelería',
            'descripcion' => 'Aprende recetas y las técnicas más requeridas para la hostelería'
        ]);

        Categoria::create([
            'nombre' => 'Lenguajes de programación',
            'descripcion' => 'Desde los lenguajes más actuales hasta los más usados por la empresa'
        ]);

    }

}
