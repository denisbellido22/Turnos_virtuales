<?php

namespace App\Http\Controllers\Atencion;

use App\Http\Controllers\Controller;
use App\Models\Turno;
use App\Services\TurnoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TurnoAccionController extends Controller
{
    public function llamarSiguiente(Request $request, TurnoService $turnos): RedirectResponse
    {
        $turno = $turnos->llamarSiguiente($request->user());

        if (! $turno) {
            return back()->with('info', 'No hay turnos pendientes en tu área en este momento.');
        }

        return back()->with(
            'success',
            "Llamando al turno {$turno->codigo} en la ventanilla {$turno->ventanilla?->codigo}."
        );
    }

    public function repetir(Request $request, Turno $turno, TurnoService $turnos): RedirectResponse
    {
        $turno = $turnos->repetirLlamado($turno, $request->user());

        return back()->with('success', "Llamado repetido para el turno {$turno->codigo}.");
    }

    public function atender(Request $request, Turno $turno, TurnoService $turnos): RedirectResponse
    {
        $turno = $turnos->marcarAtendido($turno, $request->user());

        return back()->with('success', "Turno {$turno->codigo} marcado como atendido.");
    }

    public function ausente(Request $request, Turno $turno, TurnoService $turnos): RedirectResponse
    {
        $turno = $turnos->marcarAusente($turno, $request->user());

        return back()->with('success', "Turno {$turno->codigo} marcado como ausente.");
    }

    public function atenderAudio(Request $request, Turno $turno, TurnoService $turnos): RedirectResponse
    {
        $turno = $turnos->atenderAudio($turno, $request->user());

        return back()->with('success', "Turno {$turno->codigo} atendido mediante audio.");
    }

    public function llamarAudio(Request $request, Turno $turno, TurnoService $turnos): RedirectResponse
    {
        $turno = $turnos->llamarAudio($turno, $request->user());

        return back()->with('success', "Llamado de turno {$turno->codigo} mediante audio.");
    }

    public function repetirAudio(Request $request, Turno $turno, TurnoService $turnos): RedirectResponse
    {
        $turno = $turnos->repetirAudio($turno, $request->user());

        return back()->with('success', "Llamado repetido mediante audio para el turno {$turno->codigo}.");
    }

    public function cancelar(Request $request, Turno $turno, TurnoService $turnos): RedirectResponse
    {
        $turno = $turnos->cancelar($turno, $request->user());

        return back()->with('success', "Turno {$turno->codigo} cancelado.");
    }
}
