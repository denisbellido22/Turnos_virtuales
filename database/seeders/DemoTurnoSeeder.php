<?php

namespace Database\Seeders;

use App\Models\Ciudadano;
use App\Models\Servicio;
use App\Models\Turno;
use App\Models\User;
use App\Models\Ventanilla;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoTurnoSeeder extends Seeder
{
    public function run(): void
    {
        if (Turno::query()->where('fecha', today()->toDateString())->exists()) {
            return;
        }

        $hoy = today()->toDateString();
        $servicios = Servicio::query()->get()->keyBy('prefijo');
        $ventanillas = Ventanilla::query()->get()->keyBy('codigo');
        $empleados = User::query()->get()->keyBy('email');
        $ciudadanos = Ciudadano::query()->orderBy('id')->get();

        if ($ciudadanos->count() < 8 || ! $servicios->has('INF')) {
            return;
        }

        $registros = [
            [
                'ciudadano' => $ciudadanos[0], 'servicio' => 'INF', 'estado' => Turno::ESTADO_PENDIENTE,
                'numero' => 1, 'creado' => 34, 'llamado' => null, 'ventanilla' => null, 'empleado' => null,
            ],
            [
                'ciudadano' => $ciudadanos[1], 'servicio' => 'INF', 'estado' => Turno::ESTADO_PENDIENTE,
                'numero' => 2, 'creado' => 26, 'llamado' => null, 'ventanilla' => null, 'empleado' => null,
            ],
            [
                'ciudadano' => $ciudadanos[2], 'servicio' => 'INF', 'estado' => Turno::ESTADO_PENDIENTE,
                'numero' => 3, 'creado' => 15, 'llamado' => null, 'ventanilla' => null, 'empleado' => null,
            ],
            [
                'ciudadano' => $ciudadanos[3], 'servicio' => 'INF', 'estado' => Turno::ESTADO_LLAMANDO,
                'numero' => 4, 'creado' => 22, 'llamado' => 5, 'ventanilla' => 'V-01', 'empleado' => 'maria.lopez@turnos.test',
            ],
            [
                'ciudadano' => $ciudadanos[4], 'servicio' => 'INF', 'estado' => Turno::ESTADO_ATENDIDO,
                'numero' => 5, 'creado' => 52, 'llamado' => 35, 'atendido' => 28, 'ventanilla' => 'V-01', 'empleado' => 'maria.lopez@turnos.test',
            ],
            [
                'ciudadano' => $ciudadanos[5], 'servicio' => 'RC', 'estado' => Turno::ESTADO_ATENDIDO,
                'numero' => 1, 'creado' => 60, 'llamado' => 40, 'atendido' => 32, 'ventanilla' => 'V-02', 'empleado' => 'maria.lopez@turnos.test',
            ],
            [
                'ciudadano' => $ciudadanos[6], 'servicio' => 'LC', 'estado' => Turno::ESTADO_PENDIENTE,
                'numero' => 1, 'creado' => 12, 'llamado' => null, 'ventanilla' => null, 'empleado' => null,
            ],
            [
                'ciudadano' => $ciudadanos[7], 'servicio' => 'IMP', 'estado' => Turno::ESTADO_ATENDIDO,
                'numero' => 1, 'creado' => 70, 'llamado' => 50, 'atendido' => 42, 'ventanilla' => 'V-06', 'empleado' => 'ana.flores@turnos.test',
            ],
        ];

        foreach ($registros as $registro) {
            $servicio = $servicios->get($registro['servicio']);
            $ventanilla = $registro['ventanilla'] ? $ventanillas->get($registro['ventanilla']) : null;
            $empleado = $registro['empleado'] ? $empleados->get($registro['empleado']) : null;
            $creado = now()->subMinutes($registro['creado']);
            $llamado = $registro['llamado'] !== null ? now()->subMinutes($registro['llamado']) : null;
            $atendido = isset($registro['atendido']) ? now()->subMinutes($registro['atendido']) : null;

            $turnoId = DB::table('turnos')->insertGetId([
                'codigo' => $servicio->prefijo.'-'.str_pad((string) $registro['numero'], 3, '0', STR_PAD_LEFT),
                'numero' => $registro['numero'],
                'fecha' => $hoy,
                'servicio_id' => $servicio->id,
                'area_id' => $servicio->area_id,
                'ciudadano_id' => $registro['ciudadano']->id,
                'ventanilla_id' => $ventanilla?->id,
                'user_id' => $empleado?->id,
                'estado' => $registro['estado'],
                'llamado_at' => $llamado,
                'atendido_at' => $atendido,
                'finalizado_at' => $atendido,
                'created_at' => $creado,
                'updated_at' => $atendido ?? $llamado ?? $creado,
            ]);

            DB::table('historial_turnos')->insert([
                'turno_id' => $turnoId,
                'user_id' => null,
                'ventanilla_id' => null,
                'estado_anterior' => null,
                'estado_nuevo' => Turno::ESTADO_PENDIENTE,
                'accion' => 'crear',
                'observacion' => 'Turno generado por el ciudadano.',
                'created_at' => $creado,
            ]);

            if ($llamado) {
                DB::table('historial_turnos')->insert([
                    'turno_id' => $turnoId,
                    'user_id' => $empleado?->id,
                    'ventanilla_id' => $ventanilla?->id,
                    'estado_anterior' => Turno::ESTADO_PENDIENTE,
                    'estado_nuevo' => Turno::ESTADO_LLAMANDO,
                    'accion' => 'llamar',
                    'observacion' => 'Llamado a '.$ventanilla?->codigo.'.',
                    'created_at' => $llamado,
                ]);
            }

            if ($atendido) {
                DB::table('historial_turnos')->insert([
                    'turno_id' => $turnoId,
                    'user_id' => $empleado?->id,
                    'ventanilla_id' => $ventanilla?->id,
                    'estado_anterior' => Turno::ESTADO_LLAMANDO,
                    'estado_nuevo' => Turno::ESTADO_ATENDIDO,
                    'accion' => 'atender',
                    'observacion' => 'Atención finalizada.',
                    'created_at' => $atendido,
                ]);
            }
        }
    }
}
