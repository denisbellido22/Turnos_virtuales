<?php

namespace App\Http\Controllers\Ciudadano;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTurnoRequest;
use App\Models\Ciudadano;
use App\Models\Servicio;
use App\Models\Turno;
use App\Services\TurnoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TurnoController extends Controller
{
    public function index(Request $request): View
    {
        $ciudadano = $this->ciudadano($request);
        $hoy = today()->toDateString();

        $activos = Turno::query()
            ->with(['servicio', 'area', 'ventanilla'])
            ->where('ciudadano_id', $ciudadano->id)
            ->where('fecha', $hoy)
            ->whereIn('estado', Turno::ESTADOS_ACTIVOS)
            ->latest('id')
            ->get();

        $historial = Turno::query()
            ->with(['servicio', 'area', 'ventanilla'])
            ->where('ciudadano_id', $ciudadano->id)
            ->whereNotIn('estado', Turno::ESTADOS_ACTIVOS)
            ->latest('id')
            ->limit(10)
            ->get();

        $servicios = Servicio::query()
            ->with('area')
            ->where('activo', true)
            ->whereHas('area', fn ($query) => $query->where('activo', true))
            ->orderBy('area_id')
            ->orderBy('nombre')
            ->get()
            ->groupBy(fn (Servicio $servicio) => $servicio->area->nombre);

        $posiciones = $activos->mapWithKeys(
            fn (Turno $turno) => [$turno->id => $turno->posicionEnCola()]
        );

        return view('ciudadano.turnos.index', compact(
            'ciudadano',
            'activos',
            'historial',
            'servicios',
            'posiciones',
        ));
    }

    public function store(StoreTurnoRequest $request, TurnoService $turnos): RedirectResponse
    {
        $servicio = Servicio::query()->with('area')->findOrFail($request->integer('servicio_id'));

        $turno = $turnos->crear($this->ciudadano($request), $servicio);

        return redirect()->route('ciudadano.turnos.show', $turno)
            ->with('success', "Tu turno {$turno->codigo} fue generado correctamente.");
    }

    public function show(Request $request, Turno $turno): View
    {
        $this->autorizarTurno($request, $turno);

        $turno->load(['servicio', 'area', 'ventanilla', 'historial.user']);

        $posicion = $turno->posicionEnCola();
        $historial = $turno->historial->sortBy('created_at');

        return view('ciudadano.turnos.show', compact('turno', 'posicion', 'historial'));
    }

    public function estado(Request $request, Turno $turno): JsonResponse
    {
        $this->autorizarTurno($request, $turno);

        $turno->loadMissing(['ventanilla', 'servicio', 'area']);

        return response()->json([
            'id' => $turno->id,
            'codigo' => $turno->codigo,
            'estado' => $turno->estado,
            'estado_etiqueta' => $turno->estadoEtiqueta(),
            'estado_clase' => $turno->estadoClase(),
            'posicion' => $turno->posicionEnCola(),
            'ventanilla' => $turno->ventanilla?->codigo,
            'servicio' => $turno->servicio?->nombre,
            'area' => $turno->area?->nombre,
            'actualizado_en' => now()->toIso8601String(),
        ]);
    }

    private function ciudadano(Request $request): Ciudadano
    {
        $ciudadano = $request->user()?->ciudadano;

        abort_unless($ciudadano, 403, 'Tu usuario no tiene un perfil de ciudadano.');

        return $ciudadano;
    }

    private function autorizarTurno(Request $request, Turno $turno): void
    {
        abort_unless(
            $turno->ciudadano_id === $this->ciudadano($request)->id,
            403,
            'No tienes permiso para consultar este turno.'
        );
    }
}
