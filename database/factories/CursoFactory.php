<?php

namespace Database\Factories;

use App\Models\Categoria;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Curso>
 */
class CursoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->words(3, true),
            'precio' => fake()->randomFloat(2, 5, 500),
            'vacantes' => fake()->numberBetween(5, 30),
            'fecha_inicio' => fake()->date(),
            'fecha_fin' => fake()->date(),
            'categoria_id' => Categoria::factory(),
        ];
    }


}
