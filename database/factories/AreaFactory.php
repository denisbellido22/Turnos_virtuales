<?php

namespace Database\Factories;

use App\Models\Area;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Area>
 */
class AreaFactory extends Factory
{
    protected $model = Area::class;

    public function definition(): array
    {
        return [
            'nombre' => 'Área '.fake()->unique()->word(),
            'descripcion' => fake()->sentence(),
            'activo' => true,
        ];
    }
}
