@extends('layouts.app')

@section('seccion', 'Atención')
@section('titulo', 'Panel de atención')

@section('contenido')
    <div class="page-head">
        <div>
            <span class="page-kicker">Ventanilla {{ $ventanilla?->codigo ?? 'sin asignar' }}</span>
            <h2>{{ $empleado->name }}</h2>
            <p class="mb-0 text-muted">
                @if ($ventanilla)
                    {{ $ventanilla->area?->nombre }} · {{ $ventanilla->nombre }}
                @else
                    Tu puesto de atención aún no está configurado.
                @endif
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <span class="live-pill"><span class="live-dot"></span> Conectado</span>
            <button type="button" class="btn btn-outline-secondary" onclick="window.location.reload()">
                <i class="bi bi-arrow-clockwise me-1"></i> Actualizar
            </button>
        </div>
    </div>

    @unless ($ventanilla)
        <div class="card app-card">
            <div class="card-body text-center py-5">
                <span class="empty-icon warning"><i class="bi bi-window-x"></i></span>
                <h3 class="h5 mt-3">Sin ventanilla asignada</h3>
                <p class="text-muted mb-0">Solicita al administrador que te asigne un área y una ventanilla para comenzar a llamar turnos.</p>
            </div>
        </div>
    @else
        <div id="atencion-panel" data-cola-url="{{ route('atencion.cola') }}">
            <div id="cola-contenido">
                @include('atencion._cola')
            </div>
        </div>
    @endunless
@endsection
