<?php

namespace Database\Factories;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'rol_id' => fn () => Rol::query()->firstOrCreate(
                ['nombre' => Rol::CIUDADANO],
                ['descripcion' => 'Solicita turnos y consulta su posición.']
            )->id,
            'area_id' => null,
            'ventanilla_id' => null,
            'activo' => true,
            'remember_token' => Str::random(10),
        ];
    }

    public function administrador(): static
    {
        return $this->state(fn () => [
            'rol_id' => Rol::query()->firstOrCreate(
                ['nombre' => Rol::ADMINISTRADOR],
                ['descripcion' => 'Administra usuarios, catálogos y reportes.']
            )->id,
        ]);
    }

    public function empleado(): static
    {
        return $this->state(fn () => [
            'rol_id' => Rol::query()->firstOrCreate(
                ['nombre' => Rol::EMPLEADO],
                ['descripcion' => 'Atiende turnos desde una ventanilla.']
            )->id,
        ]);
    }

    public function inactivo(): static
    {
        return $this->state(fn () => ['activo' => false]);
    }
}
