<?php

namespace App\Http\Controllers;

use App\Models\Turno;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PantallaController extends Controller
{
    public function index(): View
    {
        $llamados = $this->llamados();
        $ultimo = $llamados->first();
        $pendientes = Turno::query()->delDia()->where('estado', Turno::ESTADO_PENDIENTE)->count();

        return view('pantalla.index', compact('llamados', 'ultimo', 'pendientes'));
    }

    public function datos(): JsonResponse
    {
        return response()->json([
            'llamados' => $this->llamados()->map->resumen(),
            'pendientes' => Turno::query()->delDia()->where('estado', Turno::ESTADO_PENDIENTE)->count(),
            'actualizado_en' => now()->toIso8601String(),
        ]);
    }

    /**
     * Últimos turnos llamados del día, sin datos personales.
     */
    private function llamados(): Collection
    {
        return Turno::query()
            ->with(['servicio', 'area', 'ventanilla'])
            ->delDia()
            ->whereNotNull('llamado_at')
            ->whereIn('estado', [Turno::ESTADO_LLAMANDO, Turno::ESTADO_ATENDIDO, Turno::ESTADO_AUSENTE])
            ->orderByDesc('llamado_at')
            ->orderByDesc('id')
            ->limit(8)
            ->get();
    }
}
