<?php

namespace Database\Factories;

use App\Models\Ciudadano;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ciudadano>
 */
class CiudadanoFactory extends Factory
{
    protected $model = Ciudadano::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'nombre' => fake()->name(),
            'documento' => fake()->unique()->numerify('########'),
            'telefono' => fake()->numerify('7#######'),
            'direccion' => fake()->address(),
        ];
    }
}
