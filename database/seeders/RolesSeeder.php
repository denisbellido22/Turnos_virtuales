<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['nombre' => Rol::ADMINISTRADOR, 'descripcion' => 'Administra usuarios, catálogos y reportes.'],
            ['nombre' => Rol::EMPLEADO, 'descripcion' => 'Atiende turnos desde una ventanilla.'],
            ['nombre' => Rol::CIUDADANO, 'descripcion' => 'Solicita turnos y consulta su posición.'],
        ];

        foreach ($roles as $rol) {
            Rol::query()->updateOrCreate(['nombre' => $rol['nombre']], $rol);
        }
    }
}
