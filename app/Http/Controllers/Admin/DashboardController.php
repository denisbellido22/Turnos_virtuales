<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ciudadano;
use App\Models\Rol;
use App\Models\Turno;
use App\Models\User;
use App\Models\Ventanilla;
use App\Services\EstadisticaService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(EstadisticaService $estadisticas): View
    {
        $hoy = today()->toDateString();
        $turnosHoy = Turno::query()->where('fecha', $hoy);

        $resumen = $estadisticas->resumen($turnosHoy);
        $porArea = $estadisticas->porArea($turnosHoy);
        $serie = $estadisticas->porDia(
            Turno::query()->whereBetween('fecha', [today()->subDays(6)->toDateString(), $hoy])
        );

        $ultimos = Turno::query()
            ->with(['servicio', 'area', 'ventanilla', 'ciudadano.user'])
            ->where('fecha', $hoy)
            ->latest('updated_at')
            ->limit(8)
            ->get();

        $ventanillasActivas = Ventanilla::query()->activas()->count();
        $ventanillasTotal = Ventanilla::query()->count();
        $empleadosActivos = User::query()
            ->where('activo', true)
            ->whereHas('rol', fn ($query) => $query->where('nombre', Rol::EMPLEADO))
            ->count();
        $ciudadanosRegistrados = Ciudadano::query()->count();

        return view('admin.dashboard', compact(
            'resumen',
            'porArea',
            'serie',
            'ultimos',
            'ventanillasActivas',
            'ventanillasTotal',
            'empleadosActivos',
            'ciudadanosRegistrados',
        ));
    }
}
