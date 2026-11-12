@extends('layouts.guest')

@section('titulo', 'Tu turno '.$turno->codigo)

@php
    $mensaje = match ($turno->estado) {
        \App\Models\Turno::ESTADO_LLAMANDO => '¡Es tu turno! Acércate a la ventanilla '.($turno->ventanilla?->codigo ?? 'asignada').'.',
        \App\Models\Turno::ESTADO_ATENDIDO => 'Tu atención fue completada. ¡Gracias por utilizar el sistema!',
        \App\Models\Turno::ESTADO_AUSENTE => 'El turno fue marcado como ausente. Solicita uno nuevo si lo necesitas.',
        \App\Models\Turno::ESTADO_CANCELADO => 'Este turno fue cancelado.',
        default => $posicion === 1
            ? 'Eres el próximo en ser llamado.'
            : 'Espera tu llamado; esta pantalla se actualizará automáticamente.',
    };
@endphp

@section('contenido')
    <div id="turno-detalle" data-estado-url="{{ route('turnos.estado', $turno->codigo) }}">
        <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
            <div>
                <span class="eyebrow">Seguimiento en vivo</span>
                <h1 class="h4 mb-1">Tu turno {{ $turno->codigo }}</h1>
                <p class="small text-muted mb-0">{{ $turno->servicio?->nombre }} · {{ $turno->area?->nombre }}</p>
            </div>
            <a href="{{ route('turnos.create') }}" class="btn btn-sm btn-outline-secondary">Nuevo turno</a>
        </div>

        @include('partials.flash')

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

        <div class="position-card mt-3">
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
            <div class="live-indicator mt-3">
                <span class="live-dot"></span>
                Actualización automática por WebSocket
            </div>
        </div>

        <div class="mt-3">
            <h2 class="h6 text-uppercase text-muted letter-spacing mb-2">Historial del turno</h2>
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

        <a href="{{ route('home') }}" class="btn btn-outline-secondary w-100 mt-2">
            <i class="bi bi-arrow-left me-1"></i> Volver al inicio
        </a>
    </div>
@endsection
