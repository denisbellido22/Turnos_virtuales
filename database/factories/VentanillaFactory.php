<?php

namespace Database\Factories;

use App\Models\Area;
use App\Models\Ventanilla;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ventanilla>
 */
class VentanillaFactory extends Factory
{
    protected $model = Ventanilla::class;

    public function definition(): array
    {
        return [
            'area_id' => Area::factory(),
            'nombre' => 'Ventanilla '.fake()->unique()->numberBetween(1, 9999),
            'codigo' => 'V-'.fake()->unique()->numberBetween(100, 9999),
            'activo' => true,
        ];
    }
}
