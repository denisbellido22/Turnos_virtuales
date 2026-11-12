<?php

namespace App\Http\Controllers\Atencion;

use App\Http\Controllers\Controller;
use App\Models\Turno;
use App\Models\User;
use App\Services\EstadisticaService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PanelController extends Controller
{
    public function index(Request $request): View
    {
        return view('atencion.panel', $this->datosPanel($request->user()));
    }

    public function cola(Request $request): View
    {
        return view('atencion._cola', $this->datosPanel($request->user()));
    }

    /**
     * @return array<string, mixed>
     */
    private function datosPanel(User $empleado): array
    {
        $empleado->loadMissing(['ventanilla.area']);
        $ventanilla = $empleado->ventanilla;
        $hoy = today()->toDateString();

        $activo = null;
        $pendientes = collect();
        $totalPendientes = 0;
        $atendidosHoy = 0;
        $ausentesHoy = 0;
        $promedioAtencion = null;

        if ($ventanilla) {
            $activo = Turno::query()
                ->with(['servicio', 'area', 'ciudadano.user'])
                ->where('ventanilla_id', $ventanilla->id)
                ->where('fecha', $hoy)
                ->where('estado', Turno::ESTADO_LLAMANDO)
                ->first();

            $consultaPendientes = Turno::query()
                ->where('area_id', $ventanilla->area_id)
                ->where('fecha', $hoy)
                ->where('estado', Turno::ESTADO_PENDIENTE);

            $totalPendientes = (clone $consultaPendientes)->count();

            $pendientes = (clone $consultaPendientes)
                ->with(['servicio', 'ciudadano.user'])
                ->orderBy('created_at')
                ->orderBy('id')
                ->limit(12)
                ->get();

            $consultaVentanilla = Turno::query()
                ->where('ventanilla_id', $ventanilla->id)
                ->where('fecha', $hoy);

            $atendidosHoy = (clone $consultaVentanilla)->where('estado', Turno::ESTADO_ATENDIDO)->count();
            $ausentesHoy = (clone $consultaVentanilla)->where('estado', Turno::ESTADO_AUSENTE)->count();
            $promedioAtencion = app(EstadisticaService::class)
                ->promedioSegundos($consultaVentanilla, 'llamado_at', 'finalizado_at');
        }

        return compact(
            'empleado',
            'ventanilla',
            'activo',
            'pendientes',
            'totalPendientes',
            'atendidosHoy',
            'ausentesHoy',
            'promedioAtencion',
        );
    }
}
