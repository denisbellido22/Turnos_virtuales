<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Servicio;
use App\Models\Turno;
use App\Services\EstadisticaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ReporteController extends Controller
{
    public function __invoke(Request $request, EstadisticaService $estadisticas): View|RedirectResponse
    {
        $request->validate([
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date'],
            'area_id' => ['nullable', 'integer', 'exists:areas,id'],
            'servicio_id' => ['nullable', 'integer', 'exists:servicios,id'],
        ], [], [
            'desde' => 'fecha inicial',
            'hasta' => 'fecha final',
            'area_id' => 'área',
            'servicio_id' => 'servicio',
        ]);

        $desde = $request->filled('desde')
            ? Carbon::parse($request->input('desde'))->startOfDay()
            : today()->subDays(6)->startOfDay();

        $hasta = $request->filled('hasta')
            ? Carbon::parse($request->input('hasta'))->endOfDay()
            : today()->endOfDay();

        if ($desde->gt($hasta)) {
            return back()->withErrors(['desde' => 'La fecha inicial no puede ser posterior a la fecha final.'])->withInput();
        }

        if ($desde->diffInDays($hasta) > 366) {
            return back()->withErrors(['desde' => 'El rango de fechas no puede superar un año.'])->withInput();
        }

        $areaId = $request->integer('area_id');
        $servicioId = $request->integer('servicio_id');

        $consulta = Turno::query()
            ->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->when($areaId > 0, fn ($query) => $query->where('area_id', $areaId))
            ->when($servicioId > 0, fn ($query) => $query->where('servicio_id', $servicioId));

        $resumen = $estadisticas->resumen($consulta);
        $porDia = $estadisticas->porDia($consulta);
        $porArea = $estadisticas->porArea($consulta);
        $porServicio = $estadisticas->porServicio($consulta);

        $turnos = (clone $consulta)
            ->with(['servicio', 'area', 'ventanilla', 'ciudadano.user'])
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $areas = Area::query()->orderBy('nombre')->get();
        $servicios = Servicio::query()->with('area')->orderBy('nombre')->get();

        return view('admin.reportes', compact(
            'desde',
            'hasta',
            'areaId',
            'servicioId',
            'resumen',
            'porDia',
            'porArea',
            'porServicio',
            'turnos',
            'areas',
            'servicios',
        ));
    }
}
