<?php

namespace App\Services;

use App\Events\TurnoActualizado;
use App\Events\TurnoCreado;
use App\Events\TurnoLlamado;
use App\Models\Area;
use App\Models\Ciudadano;
use App\Models\HistorialTurno;
use App\Models\Servicio;
use App\Models\Turno;
use App\Models\User;
use App\Models\Ventanilla;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class TurnoService
{
    /**
     * Genera un turno consecutivo y único por servicio y día.
     *
     * El bloqueo del ciudadano y del servicio serializa las peticiones
     * concurrentes y evita turnos duplicados incluso sin apoyo del cliente.
     */
    public function crear(Ciudadano $ciudadano, Servicio $servicio): Turno
    {
        $turno = DB::transaction(function () use ($ciudadano, $servicio) {
            $ciudadano = Ciudadano::query()->lockForUpdate()->findOrFail($ciudadano->id);
            $servicio = Servicio::query()->with('area')->lockForUpdate()->findOrFail($servicio->id);

            if (! $servicio->activo || ! $servicio->area?->activo) {
                throw ValidationException::withMessages([
                    'servicio_id' => 'El servicio seleccionado no está disponible en este momento.',
                ]);
            }

            $hoy = today()->toDateString();

            $turnoActivo = Turno::query()
                ->where('ciudadano_id', $ciudadano->id)
                ->where('fecha', $hoy)
                ->whereIn('estado', Turno::ESTADOS_ACTIVOS)
                ->lockForUpdate()
                ->first();

            if ($turnoActivo) {
                throw ValidationException::withMessages([
                    'servicio_id' => "Ya tienes el turno {$turnoActivo->codigo} activo. Espera a ser atendido para solicitar otro.",
                ]);
            }

            $ultimoNumero = (int) Turno::query()
                ->where('servicio_id', $servicio->id)
                ->where('fecha', $hoy)
                ->max('numero');

            $numero = $ultimoNumero + 1;

            $turno = Turno::create([
                'codigo' => $servicio->prefijo.'-'.str_pad((string) $numero, 3, '0', STR_PAD_LEFT),
                'numero' => $numero,
                'fecha' => $hoy,
                'servicio_id' => $servicio->id,
                'area_id' => $servicio->area_id,
                'ciudadano_id' => $ciudadano->id,
                'estado' => Turno::ESTADO_PENDIENTE,
            ]);

            $this->registrarHistorial(
                turno: $turno,
                estadoAnterior: null,
                estadoNuevo: Turno::ESTADO_PENDIENTE,
                accion: 'crear',
                observacion: 'Turno generado por el ciudadano.',
            );

            return $turno->load(['servicio', 'area', 'ventanilla']);
        });

        Log::info('Turno generado', [
            'turno_id' => $turno->id,
            'codigo' => $turno->codigo,
            'ciudadano_id' => $turno->ciudadano_id,
            'servicio_id' => $turno->servicio_id,
        ]);

        event(new TurnoCreado($turno));

        return $turno;
    }

    /**
     * Llama al siguiente turno pendiente del área de la ventanilla del empleado.
     */
    public function llamarSiguiente(User $empleado): ?Turno
    {
        $ventanilla = $this->ventanillaDelEmpleado($empleado);

        $turno = DB::transaction(function () use ($empleado, $ventanilla) {
            $ventanilla = Ventanilla::query()->with('area')->lockForUpdate()->findOrFail($ventanilla->id);

            if (! $ventanilla->activo || ! $ventanilla->area?->activo) {
                throw ValidationException::withMessages([
                    'ventanilla' => 'La ventanilla o su área se encuentran inactivas.',
                ]);
            }

            // Serializa las llamadas del área para que dos ventanillas no tomen el mismo turno.
            Area::query()->lockForUpdate()->findOrFail($ventanilla->area_id);

            $llamadoActual = Turno::query()
                ->where('ventanilla_id', $ventanilla->id)
                ->where('estado', Turno::ESTADO_LLAMANDO)
                ->lockForUpdate()
                ->first();

            if ($llamadoActual) {
                throw ValidationException::withMessages([
                    'turno' => "Primero finaliza el turno {$llamadoActual->codigo}: márcalo como atendido o ausente.",
                ]);
            }

            $turno = Turno::query()
                ->with(['servicio', 'area', 'ciudadano.user'])
                ->where('area_id', $ventanilla->area_id)
                ->where('fecha', today()->toDateString())
                ->where('estado', Turno::ESTADO_PENDIENTE)
                ->orderBy('created_at')
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            if (! $turno) {
                return null;
            }

            $estadoAnterior = $turno->estado;

            $turno->update([
                'estado' => Turno::ESTADO_LLAMANDO,
                'ventanilla_id' => $ventanilla->id,
                'user_id' => $empleado->id,
                'llamado_at' => now(),
            ]);

            $this->registrarHistorial(
                turno: $turno,
                user: $empleado,
                ventanilla: $ventanilla,
                estadoAnterior: $estadoAnterior,
                estadoNuevo: Turno::ESTADO_LLAMANDO,
                accion: 'llamar',
                observacion: "Llamado a {$ventanilla->codigo}.",
            );

            return $turno->fresh(['servicio', 'area', 'ventanilla']);
        });

        if ($turno) {
            Log::info('Turno llamado', [
                'turno_id' => $turno->id,
                'codigo' => $turno->codigo,
                'ventanilla_id' => $ventanilla->id,
                'user_id' => $empleado->id,
            ]);

            event(new TurnoLlamado($turno));
        }

        return $turno;
    }

    public function repetirLlamado(Turno $turno, User $empleado): Turno
    {
        $ventanilla = $this->ventanillaDelEmpleado($empleado);

        $turno = DB::transaction(function () use ($turno, $empleado, $ventanilla) {
            $turno = $this->bloquearTurnoDeVentanilla($turno, $ventanilla);

            if ($turno->estado !== Turno::ESTADO_LLAMANDO) {
                throw ValidationException::withMessages([
                    'turno' => 'Solo se puede repetir el llamado de un turno que está siendo llamado.',
                ]);
            }

            $turno->update(['llamado_at' => now()]);

            $this->registrarHistorial(
                turno: $turno,
                user: $empleado,
                ventanilla: $ventanilla,
                estadoAnterior: Turno::ESTADO_LLAMANDO,
                estadoNuevo: Turno::ESTADO_LLAMANDO,
                accion: 'repetir_llamado',
                observacion: 'Llamado repetido.',
            );

            return $turno->fresh(['servicio', 'area', 'ventanilla']);
        });

        Log::info('Llamado repetido', [
            'turno_id' => $turno->id,
            'codigo' => $turno->codigo,
            'ventanilla_id' => $ventanilla->id,
        ]);

        event(new TurnoLlamado($turno));

        return $turno;
    }

    public function marcarAtendido(Turno $turno, User $empleado): Turno
    {
        $ventanilla = $this->ventanillaDelEmpleado($empleado);

        $turno = DB::transaction(function () use ($turno, $empleado, $ventanilla) {
            $turno = $this->bloquearTurnoDeVentanilla($turno, $ventanilla);

            if ($turno->estado !== Turno::ESTADO_LLAMANDO) {
                throw ValidationException::withMessages([
                    'turno' => 'Solo se puede marcar como atendido un turno que está siendo llamado.',
                ]);
            }

            $turno->update([
                'estado' => Turno::ESTADO_ATENDIDO,
                'atendido_at' => now(),
                'finalizado_at' => now(),
            ]);

            $this->registrarHistorial(
                turno: $turno,
                user: $empleado,
                ventanilla: $ventanilla,
                estadoAnterior: Turno::ESTADO_LLAMANDO,
                estadoNuevo: Turno::ESTADO_ATENDIDO,
                accion: 'atender',
                observacion: 'Atención finalizada.',
            );

            return $turno->fresh(['servicio', 'area', 'ventanilla']);
        });

        Log::info('Turno atendido', [
            'turno_id' => $turno->id,
            'codigo' => $turno->codigo,
            'ventanilla_id' => $ventanilla->id,
        ]);

        event(new TurnoActualizado($turno));

        return $turno;
    }

    public function marcarAusente(Turno $turno, User $empleado): Turno
    {
        $ventanilla = $this->ventanillaDelEmpleado($empleado);

        $turno = DB::transaction(function () use ($turno, $empleado, $ventanilla) {
            $turno = $this->bloquearTurnoDeVentanilla($turno, $ventanilla);

            if ($turno->estado !== Turno::ESTADO_LLAMANDO) {
                throw ValidationException::withMessages([
                    'turno' => 'Solo se puede marcar como ausente un turno que está siendo llamado.',
                ]);
            }

            $turno->update([
                'estado' => Turno::ESTADO_AUSENTE,
                'finalizado_at' => now(),
            ]);

            $this->registrarHistorial(
                turno: $turno,
                user: $empleado,
                ventanilla: $ventanilla,
                estadoAnterior: Turno::ESTADO_LLAMANDO,
                estadoNuevo: Turno::ESTADO_AUSENTE,
                accion: 'ausente',
                observacion: 'El ciudadano no se presentó al llamado.',
            );

            return $turno->fresh(['servicio', 'area', 'ventanilla']);
        });

        Log::info('Turno marcado como ausente', [
            'turno_id' => $turno->id,
            'codigo' => $turno->codigo,
            'ventanilla_id' => $ventanilla->id,
        ]);

        event(new TurnoActualizado($turno));

        return $turno;
    }

    public function cancelar(Turno $turno, User $empleado): Turno
    {
        $ventanilla = $this->ventanillaDelEmpleado($empleado);

        $turno = DB::transaction(function () use ($turno, $empleado, $ventanilla) {
            $turno = Turno::query()->with('ventanilla')->lockForUpdate()->findOrFail($turno->id);

            if ($turno->area_id !== $ventanilla->area_id) {
                throw new AuthorizationException('El turno no pertenece al área de tu ventanilla.');
            }

            if ($turno->estado !== Turno::ESTADO_PENDIENTE) {
                throw ValidationException::withMessages([
                    'turno' => 'Solo se pueden cancelar turnos que aún están en espera.',
                ]);
            }

            $turno->update([
                'estado' => Turno::ESTADO_CANCELADO,
                'finalizado_at' => now(),
            ]);

            $this->registrarHistorial(
                turno: $turno,
                user: $empleado,
                ventanilla: $ventanilla,
                estadoAnterior: Turno::ESTADO_PENDIENTE,
                estadoNuevo: Turno::ESTADO_CANCELADO,
                accion: 'cancelar',
                observacion: 'Turno cancelado desde el panel de atención.',
            );

            return $turno->fresh(['servicio', 'area', 'ventanilla']);
        });

        Log::info('Turno cancelado', [
            'turno_id' => $turno->id,
            'codigo' => $turno->codigo,
            'ventanilla_id' => $ventanilla->id,
        ]);

        event(new TurnoActualizado($turno));

        return $turno;
    }

    /**
     * Atiende un turno mediante llamada de audio.
     */
    public function atenderAudio(Turno $turno, User $empleado): Turno
    {
        $ventanilla = $this->ventanillaDelEmpleado($empleado);

        $turno = DB::transaction(function () use ($turno, $empleado, $ventanilla) {
            $turno = $this->bloquearTurnoDeVentanilla($turno, $ventanilla);

            if ($turno->estado !== Turno::ESTADO_LLAMANDO) {
                throw ValidationException::withMessages([
                    'turno' => 'Solo se puede marcar como atendido un turno que está siendo llamado.',
                ]);
            }

            $turno->update([
                'estado' => Turno::ESTADO_ATENDIDO,
                'atendido_at' => now(),
                'finalizado_at' => now(),
            ]);

            $this->registrarHistorial(
                turno: $turno,
                user: $empleado,
                ventanilla: $ventanilla,
                estadoAnterior: Turno::ESTADO_LLAMANDO,
                estadoNuevo: Turno::ESTADO_ATENDIDO,
                accion: 'atender_audio',
                observacion: 'Atención finalizada mediante llamada de audio.',
            );

            return $turno->fresh(['servicio', 'area', 'ventanilla']);
        });

        Log::info('Turno atendido por audio', [
            'turno_id' => $turno->id,
            'codigo' => $turno->codigo,
            'ventanilla_id' => $ventanilla->id,
        ]);

        event(new TurnoActualizado($turno));

        return $turno;
    }

    /**
     * Llama a un turno mediante audio (re-inicia el llamado).
     */
    public function llamarAudio(Turno $turno, User $empleado): Turno
    {
        $ventanilla = $this->ventanillaDelEmpleado($empleado);

        $turno = DB::transaction(function () use ($turno, $empleado, $ventanilla) {
            $turno = $this->bloquearTurnoDeVentanilla($turno, $ventanilla);

            if ($turno->estado !== Turno::ESTADO_PENDIENTE) {
                throw ValidationException::withMessages([
                    'turno' => 'Solo se puede llamar un turno que está en estado de espera.',
                ]);
            }

            $turno->update([
                'estado' => Turno::ESTADO_LLAMANDO,
                'ventanilla_id' => $ventanilla->id,
                'user_id' => $empleado->id,
                'llamado_at' => now(),
            ]);

            $this->registrarHistorial(
                turno: $turno,
                user: $empleado,
                ventanilla: $ventanilla,
                estadoAnterior: $turno->estado,
                estadoNuevo: Turno::ESTADO_LLAMANDO,
                accion: 'llamar_audio',
                observacion: 'Llamado realizado mediante llamada de audio.',
            );

            return $turno->fresh(['servicio', 'area', 'ventanilla']);
        });

        Log::info('Turno llamado por audio', [
            'turno_id' => $turno->id,
            'codigo' => $turno->codigo,
            'ventanilla_id' => $ventanilla->id,
        ]);

        event(new TurnoLlamado($turno));

        return $turno;
    }

    /**
     * Repite el llamado de un turno mediante audio.
     */
    public function repetirAudio(Turno $turno, User $empleado): Turno
    {
        $ventanilla = $this->ventanillaDelEmpleado($empleado);

        $turno = DB::transaction(function () use ($turno, $empleado, $ventanilla) {
            $turno = $this->bloquearTurnoDeVentanilla($turno, $ventanilla);

            if ($turno->estado !== Turno::ESTADO_LLAMANDO) {
                throw ValidationException::withMessages([
                    'turno' => 'Solo se puede repetir el llamado de un turno que está siendo llamado.',
                ]);
            }

            $turno->update(['llamado_at' => now()]);

            $this->registrarHistorial(
                turno: $turno,
                user: $empleado,
                ventanilla: $ventanilla,
                estadoAnterior: Turno::ESTADO_LLAMANDO,
                estadoNuevo: Turno::ESTADO_LLAMANDO,
                accion: 'repetir_llamado_audio',
                observacion: 'Llamado repetido mediante llamada de audio.',
            );

            return $turno->fresh(['servicio', 'area', 'ventanilla']);
        });

        Log::info('Llamado repetido por audio', [
            'turno_id' => $turno->id,
            'codigo' => $turno->codigo,
            'ventanilla_id' => $ventanilla->id,
        ]);

        event(new TurnoLlamado($turno));

        return $turno;
    }

    private function ventanillaDelEmpleado(User $empleado): Ventanilla
    {
        $ventanilla = $empleado->ventanilla;

        if (! $ventanilla) {
            throw ValidationException::withMessages([
                'ventanilla' => 'No tienes una ventanilla asignada. Solicita al administrador que configure tu puesto.',
            ]);
        }

        return $ventanilla;
    }

    private function bloquearTurnoDeVentanilla(Turno $turno, Ventanilla $ventanilla): Turno
    {
        $turno = Turno::query()->lockForUpdate()->findOrFail($turno->id);

        if ($turno->ventanilla_id !== $ventanilla->id) {
            throw new AuthorizationException('Este turno no pertenece a tu ventanilla.');
        }

        return $turno;
    }

    private function registrarHistorial(
        Turno $turno,
        ?string $estadoAnterior,
        string $estadoNuevo,
        string $accion,
        ?string $observacion = null,
        ?User $user = null,
        ?Ventanilla $ventanilla = null,
    ): void {
        HistorialTurno::create([
            'turno_id' => $turno->id,
            'user_id' => $user?->id,
            'ventanilla_id' => $ventanilla?->id,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $estadoNuevo,
            'accion' => $accion,
            'observacion' => $observacion,
        ]);
    }
}
