@extends('layouts.app')

@section('seccion', 'Ciudadano')
@section('titulo', 'Mi turno')

@php
    $mensaje = match ($turno->estado) {
        \App\Models\Turno::ESTADO_LLAMANDO => '¡Es tu turno! Acércate a la ventanilla '.($turno->ventanilla?->codigo ?? 'asignada').'.',
        \App\Models\Turno::ESTADO_ATENDIDO => 'Tu atención fue completada. ¡Gracias por utilizar el sistema!',
        \App\Models\Turno::ESTADO_AUSENTE => 'El turno fue marcado como ausente. Solicita uno nuevo si lo necesitas.',
        \App\Models\Turno::ESTADO_CANCELADO => 'Este turno fue cancelado.',
        default => $posicion === 1
            ? 'Eres el próximo en ser llamado.'
            : 'Espera tu llamado; tu pantalla se actualizará automáticamente.',
    };
@endphp

@section('contenido')
    <div id="turno-detalle" data-estado-url="{{ route('ciudadano.turnos.estado', $turno) }}">
        <div class="page-head">
            <div>
                <span class="page-kicker">Seguimiento en vivo</span>
                <h2>Tu turno {{ $turno->codigo }}</h2>
                <p class="mb-0 text-muted">{{ $turno->servicio?->nombre }} · {{ $turno->area?->nombre }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('ciudadano.turnos.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Mis turnos
                </a>
                <a href="{{ route('pantalla.index') }}" target="_blank" rel="noopener" class="btn btn-outline-secondary d-none d-sm-inline-flex">
                    <i class="bi bi-display me-1"></i> Pantalla
                </a>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="ticket-display {{ $turno->estado === \App\Models\Turno::ESTADO_LLAMANDO ? 'ticket-display-calling' : '' }}">
                    <div class="ticket-display-top">
                        <span class="ticket-display-label">Tu número de turno</span>
                        <span class="live-pill live-pill-dark"><span class="live-dot"></span> En vivo</span>
                    </div>

                    <div class="ticket-display-code">{{ $turno->codigo }}</div>

                    <div id="estado-badge" class="mb-3">
                        <x-estado-turno :estado="$turno->estado" />
                    </div>

                    <p id="turno-mensaje" class="ticket-display-message">{{ $mensaje }}</p>

                    <div class="ticket-display-grid">
                        <div>
                            <span class="hero-meta-label">Servicio</span>
                            <strong>{{ $turno->servicio?->nombre }}</strong>
                        </div>
                        <div>
                            <span class="hero-meta-label">Área</span>
                            <strong>{{ $turno->area?->nombre }}</strong>
                        </div>
                        <div>
                            <span class="hero-meta-label">Ventanilla</span>
                            <strong id="ventanilla-valor">{{ $turno->ventanilla?->codigo ?? 'Por asignar' }}</strong>
                        </div>
                        <div>
                            <span class="hero-meta-label">Solicitado</span>
                            <strong>{{ $turno->created_at->format('H:i') }}</strong>
                        </div>
                    </div>
                </div>

                <div class="card app-card mt-4">
                    <div class="card-header">
                        <h3 class="card-title">Historial del turno</h3>
                        <p class="card-subtitle">Movimientos registrados durante la atención.</p>
                    </div>
                    <div class="card-body">
                        <ol class="timeline">
                            @foreach ($historial as $evento)
                                <li class="timeline-item">
                                    <span class="timeline-dot">
                                        <i class="bi {{ match ($evento->accion) {
                                            'crear' => 'bi-plus-lg',
                                            'llamar' => 'bi-megaphone',
                                            'repetir_llamado' => 'bi-arrow-repeat',
                                            'atender' => 'bi-check2',
                                            'ausente' => 'bi-person-x',
                                            'cancelar' => 'bi-x-lg',
                                            default => 'bi-dot',
                                        } }}"></i>
                                    </span>
                                    <div class="timeline-content">
                                        <div class="d-flex justify-content-between gap-2">
                                            <strong>{{ $evento->accionEtiqueta() }}</strong>
                                            <span class="small text-muted">{{ $evento->created_at->format('H:i') }}</span>
                                        </div>
                                        @if ($evento->observacion)
                                            <p class="mb-0 small text-muted">{{ $evento->observacion }}</p>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="position-card mb-4">
                    <span class="ticket-label">Tu posición en la cola</span>
                    <div id="posicion-valor" class="position-value">{{ $posicion ?? '—' }}</div>
                    <p id="posicion-texto" class="position-note mb-0">
                        @if ($posicion)
                            @if ($posicion === 1)
                                Eres el siguiente en la fila del área.
                            @else
                                Hay {{ $posicion - 1 }} {{ $posicion - 1 === 1 ? 'persona' : 'personas' }} antes que tú.
                            @endif
                        @else
                            El turno ya no se encuentra en espera.
                        @endif
                    </p>
                    <div class="live-indicator mt-4">
                        <span class="live-dot"></span>
                        Actualización automática por WebSocket
                    </div>
                </div>

                <div class="card app-card mb-4">
                    <div class="card-body">
                        <span class="side-icon"><i class="bi bi-info-circle"></i></span>
                        <h3>Recomendaciones</h3>
                        <ul class="check-list">
                            <li><i class="bi bi-check2"></i> Mantén esta pantalla abierta para ver tu posición.</li>
                            <li><i class="bi bi-check2"></i> Cuando aparezca «Llamando», dirígete a la ventanilla indicada.</li>
                            <li><i class="bi bi-check2"></i> Si no puedes asistir, solicita un turno nuevo.</li>
                        </ul>
                    </div>
                </div>

                <div class="card app-card">
                    <div class="card-body">
                        <dl class="account-data mb-0">
                            <div><dt>Turno</dt><dd>{{ $turno->codigo }}</dd></div>
                            <div><dt>Fecha</dt><dd>{{ $turno->fecha->format('d/m/Y') }}</dd></div>
                            <div><dt>Ciudadano</dt><dd>{{ auth()->user()->name }}</dd></div>
                            <div><dt>Última actualización</dt><dd id="actualizado-en">{{ $turno->updated_at->format('H:i:s') }}</dd></div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
