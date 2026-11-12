<?php

namespace App\Services;

use App\Models\Area;
use App\Models\Servicio;
use App\Models\Turno;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EstadisticaService
{
    /**
     * @return array{total: int, pendientes: int, atendidos: int, ausentes: int, cancelados: int, promedio_espera: ?int, promedio_atencion: ?int}
     */
    public function resumen(Builder $turnos): array
    {
        return [
            'total' => (clone $turnos)->count(),
            'pendientes' => (clone $turnos)->where('estado', Turno::ESTADO_PENDIENTE)->count(),
            'atendidos' => (clone $turnos)->where('estado', Turno::ESTADO_ATENDIDO)->count(),
            'ausentes' => (clone $turnos)->where('estado', Turno::ESTADO_AUSENTE)->count(),
            'cancelados' => (clone $turnos)->where('estado', Turno::ESTADO_CANCELADO)->count(),
            'promedio_espera' => $this->promedioSegundos($turnos, 'created_at', 'llamado_at'),
            'promedio_atencion' => $this->promedioSegundos($turnos, 'llamado_at', 'finalizado_at'),
        ];
    }

    /**
     * Turnos agrupados por área, incluyendo áreas sin movimiento.
     */
    public function porArea(Builder $turnos): Collection
    {
        $filas = (clone $turnos)
            ->select('area_id')
            ->selectRaw("COUNT(*) as total")
            ->selectRaw("SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes")
            ->selectRaw("SUM(CASE WHEN estado = 'atendido' THEN 1 ELSE 0 END) as atendidos")
            ->selectRaw("SUM(CASE WHEN estado IN ('ausente', 'cancelado') THEN 1 ELSE 0 END) as no_presentados")
            ->groupBy('area_id')
            ->get()
            ->keyBy('area_id');

        return Area::query()
            ->orderBy('nombre')
            ->get()
            ->map(function (Area $area) use ($filas) {
                $fila = $filas->get($area->id);

                return (object) [
                    'area' => $area,
                    'total' => (int) ($fila->total ?? 0),
                    'pendientes' => (int) ($fila->pendientes ?? 0),
                    'atendidos' => (int) ($fila->atendidos ?? 0),
                    'no_presentados' => (int) ($fila->no_presentados ?? 0),
                ];
            });
    }

    /**
     * Turnos agrupados por día.
     */
    public function porDia(Builder $turnos): Collection
    {
        return (clone $turnos)
            ->select('fecha')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN estado = 'atendido' THEN 1 ELSE 0 END) as atendidos")
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();
    }

    /**
     * Turnos agrupados por servicio.
     */
    public function porServicio(Builder $turnos): Collection
    {
        $filas = (clone $turnos)
            ->select('servicio_id')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN estado = 'atendido' THEN 1 ELSE 0 END) as atendidos")
            ->groupBy('servicio_id')
            ->get();

        $servicios = Servicio::query()
            ->with('area')
            ->whereIn('id', $filas->pluck('servicio_id'))
            ->get()
            ->keyBy('id');

        return $filas->map(function ($fila) use ($servicios) {
            return (object) [
                'servicio' => $servicios->get($fila->servicio_id),
                'total' => (int) $fila->total,
                'atendidos' => (int) $fila->atendidos,
            ];
        });
    }

    /**
     * Promedio en segundos calculado en la base de datos, compatible con MySQL y SQLite.
     */
    public function promedioSegundos(Builder $turnos, string $desde, string $hasta): ?int
    {
        $driver = DB::connection()->getDriverName();

        $expresion = $driver === 'sqlite'
            ? "AVG((julianday({$hasta}) - julianday({$desde})) * 86400)"
            : "AVG(TIMESTAMPDIFF(SECOND, {$desde}, {$hasta}))";

        $fila = (clone $turnos)
            ->whereNotNull($desde)
            ->whereNotNull($hasta)
            ->selectRaw("{$expresion} as promedio")
            ->first();

        return $fila?->promedio !== null ? (int) round($fila->promedio) : null;
    }

    public static function duracion(?int $segundos): string
    {
        if ($segundos === null) {
            return 'Sin datos';
        }

        if ($segundos < 60) {
            return $segundos.' s';
        }

        $minutos = intdiv($segundos, 60);
        $resto = $segundos % 60;

        if ($minutos < 60) {
            return $resto > 0 ? "{$minutos} min {$resto} s" : "{$minutos} min";
        }

        $horas = intdiv($minutos, 60);
        $minutos = $minutos % 60;

        return $minutos > 0 ? "{$horas} h {$minutos} min" : "{$horas} h";
    }
}
