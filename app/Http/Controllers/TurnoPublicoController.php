<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTurnoPublicoRequest;
use App\Models\Ciudadano;
use App\Models\Servicio;
use App\Models\Turno;
use App\Services\TurnoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class TurnoPublicoController extends Controller
{
    public function create(): View
    {
        return view('turnos.solicitar', ['servicios' => $this->servicios()]);
    }

    public function store(StoreTurnoPublicoRequest $request, TurnoService $turnos): RedirectResponse
    {
        $datos = $request->validated();

        $servicio = Servicio::query()->with('area')->findOrFail($datos['servicio_id']);

        // El ciudadano se identifica por documento: no necesita cuenta para obtener un turno.
        $ciudadano = Ciudadano::query()->firstOrNew(['documento' => $datos['documento']]);
        $ciudadano->nombre = $datos['nombre'];

        if (! empty($datos['telefono'])) {
            $ciudadano->telefono = $datos['telefono'];
        }

        $ciudadano->save();

        $turno = $turnos->crear($ciudadano, $servicio);

        return redirect()->route('turnos.show', $turno->codigo)
            ->with('success', "Tu turno {$turno->codigo} fue generado correctamente.");
    }

    /**
     * Atajo para consultar un turno escribiendo su código (usado en la página de inicio).
     */
    public function buscar(Request $request): RedirectResponse
    {
        $codigo = strtoupper(trim((string) $request->query('codigo')));

        $turno = Turno::query()
            ->where('codigo', $codigo)
            ->where('fecha', today()->toDateString())
            ->first();

        if (! $turno) {
            return redirect()->route('turnos.create')
                ->with('error', 'No encontramos un turno de hoy con ese código. Si aún no tienes uno, solicítalo aquí.');
        }

        return redirect()->route('turnos.show', $turno->codigo);
    }

    public function show(string $codigo): View
    {
        $turno = $this->turnoDeHoy($codigo);
        $turno->load(['servicio', 'area', 'ventanilla', 'historial']);

        return view('turnos.show', [
            'turno' => $turno,
            'posicion' => $turno->posicionEnCola(),
            'historial' => $turno->historial->sortBy('created_at'),
        ]);
    }

    public function estado(string $codigo): JsonResponse
    {
        $turno = $this->turnoDeHoy($codigo);
        $turno->loadMissing(['servicio', 'area', 'ventanilla']);

        return response()->json([
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

    /**
     * El código de turno solo es único dentro del día, así que se busca en la fecha actual.
     */
    private function turnoDeHoy(string $codigo): Turno
    {
        return Turno::query()
            ->where('codigo', $codigo)
            ->where('fecha', today()->toDateString())
            ->firstOrFail();
    }

    /**
     * Servicios activos agrupados por área para el formulario público.
     */
    private function servicios(): Collection
    {
        return Servicio::query()
            ->with('area')
            ->where('activo', true)
            ->whereHas('area', fn ($query) => $query->where('activo', true))
            ->orderBy('area_id')
            ->orderBy('nombre')
            ->get()
            ->groupBy(fn (Servicio $servicio) => $servicio->area->nombre);
    }
}
