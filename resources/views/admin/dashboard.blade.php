@extends('layouts.app')

@section('seccion', 'Administración')
@section('titulo', 'Dashboard')

@section('contenido')
    <div class="page-head">
        <div>
            <span class="page-kicker">Resumen operativo</span>
            <h2>Hoy, {{ now()->translatedFormat('d \d\e F \d\e Y') }}</h2>
            <p class="mb-0 text-muted">Estado de la atención, colas y rendimiento del día.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.reportes.index') }}" class="btn btn-brand">
                <i class="bi bi-bar-chart-line me-1"></i> Ver reportes
            </a>
            <a href="{{ route('pantalla.index') }}" target="_blank" rel="noopener" class="btn btn-outline-secondary">
                <i class="bi bi-display me-1"></i> Pantalla pública
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <x-stat-card titulo="Turnos de hoy" :valor="$resumen['total']" icono="bi-ticket-perforated" color="primary"
                         :detalle="$ciudadanosRegistrados.' ciudadanos registrados'" />
        </div>
        <div class="col-6 col-xl-3">
            <x-stat-card titulo="En espera" :valor="$resumen['pendientes']" icono="bi-hourglass-split" color="warning"
                         :detalle="$resumen['ausentes'].' ausentes · '.$resumen['cancelados'].' cancelados'" />
        </div>
        <div class="col-6 col-xl-3">
            <x-stat-card titulo="Atendidos" :valor="$resumen['atendidos']" icono="bi-check2-circle" color="success"
                         :detalle="$resumen['total'] > 0 ? round($resumen['atendidos'] / $resumen['total'] * 100).'% del total' : 'Sin turnos'" />
        </div>
        <div class="col-6 col-xl-3">
            <x-stat-card titulo="Espera promedio" :valor="\App\Services\EstadisticaService::duracion($resumen['promedio_espera'])"
                         icono="bi-stopwatch" color="info"
                         :detalle="'Atención: '.\App\Services\EstadisticaService::duracion($resumen['promedio_atencion'])" />
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <div class="card app-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="card-title">Cola por área</h3>
                        <p class="card-subtitle">Turnos registrados hoy y su estado actual.</p>
                    </div>
                    <span class="badge rounded-pill text-bg-light">{{ $porArea->count() }} áreas</span>
                </div>
                <div class="table-responsive">
                    <table class="table app-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Área</th>
                                <th class="text-center">En espera</th>
                                <th class="text-center">Atendidos</th>
                                <th class="text-center">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($porArea as $fila)
                                <tr>
                                    <td>
                                        <strong>{{ $fila->area->nombre }}</strong>
                                        <span class="d-block small text-muted">{{ $fila->no_presentados }} no presentados</span>
                                    </td>
                                    <td class="text-center">
                                        @if ($fila->pendientes > 0)
                                            <span class="badge rounded-pill text-bg-warning">{{ $fila->pendientes }}</span>
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $fila->atendidos }}</td>
                                    <td class="text-center fw-semibold">{{ $fila->total }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">No hay áreas registradas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card app-card h-100">
                <div class="card-header">
                    <h3 class="card-title">Últimos 7 días</h3>
                    <p class="card-subtitle">Volumen de turnos generados.</p>
                </div>
                <div class="card-body">
                    @php
                        $porFecha = $serie->keyBy(fn ($fila) => $fila->fecha instanceof \Carbon\CarbonInterface
                            ? $fila->fecha->toDateString()
                            : (string) $fila->fecha);
                        $maximo = max(1, (int) $serie->max('total'));
                        $dias = collect(range(6, 0))->map(fn ($diasAtras) => today()->subDays($diasAtras));
                    @endphp

                    @if ($serie->isEmpty())
                        <div class="empty-state compact">
                            <i class="bi bi-bar-chart"></i>
                            <p>Sin datos en el período.</p>
                        </div>
                    @else
                        <div class="chart-bars">
                            @foreach ($dias as $dia)
                                @php
                                    $fila = $porFecha->get($dia->toDateString());
                                    $total = (int) ($fila->total ?? 0);
                                    $altura = $total > 0 ? max(8, round($total / $maximo * 100)) : 3;
                                @endphp
                                <div class="chart-bar-col" title="{{ $dia->format('d/m/Y') }}: {{ $total }} turnos">
                                    <span class="chart-value">{{ $total }}</span>
                                    <div class="chart-bar-track">
                                        <div class="chart-bar-fill" style="height: {{ $altura }}%"></div>
                                    </div>
                                    <span class="chart-label">{{ $dia->translatedFormat('D') }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="d-flex justify-content-between border-top pt-3 mt-3 small text-muted">
                        <span><i class="bi bi-window-stack me-1"></i>{{ $ventanillasActivas }} de {{ $ventanillasTotal }} ventanillas activas</span>
                        <span><i class="bi bi-person-badge me-1"></i>{{ $empleadosActivos }} empleados</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card app-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h3 class="card-title">Actividad reciente</h3>
                <p class="card-subtitle">Últimos turnos con movimiento el día de hoy.</p>
            </div>
            <a href="{{ route('admin.reportes.index') }}" class="btn btn-sm btn-outline-secondary">Ver todos</a>
        </div>
        <div class="table-responsive">
            <table class="table app-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Turno</th>
                        <th>Servicio</th>
                        <th>Ciudadano</th>
                        <th>Ventanilla</th>
                        <th>Estado</th>
                        <th class="text-end">Actualizado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ultimos as $turno)
                        <tr>
                            <td><span class="ticket-mini">{{ $turno->codigo }}</span></td>
                            <td>
                                <strong>{{ $turno->servicio?->nombre }}</strong>
                                <span class="d-block small text-muted">{{ $turno->area?->nombre }}</span>
                            </td>
                            <td>{{ $turno->ciudadano?->user?->name ?? '—' }}</td>
                            <td>{{ $turno->ventanilla?->codigo ?? '—' }}</td>
                            <td><x-estado-turno :estado="$turno->estado" /></td>
                            <td class="text-end text-muted small">{{ $turno->updated_at->format('H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Todavía no hay turnos registrados hoy.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
