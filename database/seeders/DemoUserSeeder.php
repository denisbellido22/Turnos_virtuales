<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Ciudadano;
use App\Models\Rol;
use App\Models\User;
use App\Models\Ventanilla;
use Illuminate\Database\Seeder;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Rol::query()->pluck('id', 'nombre');

        User::query()->updateOrCreate(
            ['email' => 'admin@turnos.test'],
            [
                'name' => 'Administrador General',
                'password' => 'Admin123!',
                'rol_id' => $roles[Rol::ADMINISTRADOR],
                'area_id' => null,
                'ventanilla_id' => null,
                'activo' => true,
            ]
        );

        $empleados = [
            ['name' => 'María López', 'email' => 'maria.lopez@turnos.test', 'area' => 'Atención al Ciudadano', 'ventanilla' => 'V-01'],
            ['name' => 'Carlos Ramírez', 'email' => 'carlos.ramirez@turnos.test', 'area' => 'Licencias y Permisos', 'ventanilla' => 'V-04'],
            ['name' => 'Ana Flores', 'email' => 'ana.flores@turnos.test', 'area' => 'Recaudación y Pagos', 'ventanilla' => 'V-06'],
        ];

        foreach ($empleados as $empleado) {
            $area = Area::query()->where('nombre', $empleado['area'])->firstOrFail();
            $ventanilla = Ventanilla::query()->where('codigo', $empleado['ventanilla'])->firstOrFail();

            User::query()->updateOrCreate(
                ['email' => $empleado['email']],
                [
                    'name' => $empleado['name'],
                    'password' => 'Empleado123!',
                    'rol_id' => $roles[Rol::EMPLEADO],
                    'area_id' => $area->id,
                    'ventanilla_id' => $ventanilla->id,
                    'activo' => true,
                ]
            );
        }

        $ciudadanos = [
            ['name' => 'Ciudadano de Prueba', 'email' => 'ciudadano@turnos.test', 'documento' => '10000001'],
            ['name' => 'Juan Pérez', 'email' => 'juan.perez@turnos.test', 'documento' => '10000002'],
            ['name' => 'Lucía Gómez', 'email' => 'lucia.gomez@turnos.test', 'documento' => '10000003'],
            ['name' => 'Pedro Salazar', 'email' => 'pedro.salazar@turnos.test', 'documento' => '10000004'],
            ['name' => 'Ana Rojas', 'email' => 'ana.rojas@turnos.test', 'documento' => '10000005'],
            ['name' => 'Luis Mendoza', 'email' => 'luis.mendoza@turnos.test', 'documento' => '10000006'],
            ['name' => 'Sofía Vargas', 'email' => 'sofia.vargas@turnos.test', 'documento' => '10000007'],
            ['name' => 'Diego Castro', 'email' => 'diego.castro@turnos.test', 'documento' => '10000008'],
        ];

        foreach ($ciudadanos as $indice => $ciudadano) {
            $user = User::query()->updateOrCreate(
                ['email' => $ciudadano['email']],
                [
                    'name' => $ciudadano['name'],
                    'password' => 'Ciudadano123!',
                    'rol_id' => $roles[Rol::CIUDADANO],
                    'area_id' => null,
                    'ventanilla_id' => null,
                    'activo' => true,
                ]
            );

            Ciudadano::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'documento' => $ciudadano['documento'],
                    'telefono' => '7000000'.$indice,
                    'direccion' => 'Av. Principal #'.(100 + $indice),
                ]
            );
        }
    }
}
