<?php

namespace Database\Factories;

use App\Models\Area;
use App\Models\Servicio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Servicio>
 */
class ServicioFactory extends Factory
{
    protected $model = Servicio::class;

    public function definition(): array
    {
        return [
            'area_id' => Area::factory(),
            'nombre' => 'Servicio '.fake()->unique()->word(),
            'prefijo' => strtoupper(fake()->unique()->bothify('??#')),
            'descripcion' => fake()->sentence(),
            'activo' => true,
        ];
    }
}
