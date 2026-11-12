@extends('layouts.app')

@section('seccion', 'Análisis')
@section('titulo', 'Estadísticas y reportes')

@section('contenido')
    <div class="page-head">
        <div>
            <span class="page-kicker">Análisis operativo</span>
            <h2>Estadísticas y reportes</h2>
            <p class="mb-0 text-muted">Del {{ $desde->format('d/m/Y') }} al {{ $hasta->format('d/m/Y') }}.</p>
        </div>
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Imprimir
        </button>
    </div>

    <div class="card app-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reportes.index') }}" class="row g-3 align-items-end">
                <div class="col-6 col-lg-2">
                    <label for="desde" class="form-label">Desde</label>
                    <input id="desde" type="date" name="desde" value="{{ $desde->toDateString() }}" class="form-control">
                </div>
                <div class="col-6 col-lg-2">
                    <label for="hasta" class="form-label">Hasta</label>
                    <input id="hasta" type="date" name="hasta" value="{{ $hasta->toDateString() }}" class="form-control">
                </div>
                <div class="col-12 col-lg-3">
                    <label for="area_id" class="form-label">Área</label>
                    <select id="area_id" name="area_id" class="form-select">
                        <option value="">Todas las áreas</option>
                        @foreach ($areas as $area)
                            <option value="{{ $area->id }}" @selected($areaId === $area->id)>{{ $area->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-lg-3">
                    <label for="servicio_id" class="form-label">Servicio</label>
                    <select id="servicio_id" name="servicio_id" class="form-select">
                        <option value="">Todos los servicios</option>
                        @foreach ($servicios as $servicio)
                            <option value="{{ $servicio->id }}" @selected($servicioId === $servicio->id)>
                                {{ $servicio->area?->nombre }} · {{ $servicio->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-lg-2 d-flex gap-2">
                    <button class="btn btn-brand flex-grow-1">Aplicar</button>
                    <a href="{{ route('admin.reportes.index') }}" class="btn btn-outline-secondary" title="Restablecer">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-2">
            <x-stat-card titulo="Turnos" :valor="$resumen['total']" icono="bi-ticket-perforated" color="primary" />
        </div>
        <div class="col-6 col-lg-2">
            <x-stat-card titulo="Atendidos" :valor="$resumen['atendidos']" icono="bi-check2-circle" color="success" />
        </div>
        <div class="col-6 col-lg-2">
            <x-stat-card titulo="En espera" :valor="$resumen['pendientes']" icono="bi-hourglass-split" color="warning" />
        </div>
        <div class="col-6 col-lg-2">
            <x-stat-card titulo="No presentados" :valor="$resumen['ausentes'] + $resumen['cancelados']" icono="bi-person-x" color="danger" />
        </div>
        <div class="col-6 col-lg-2">
            <x-stat-card titulo="Espera promedio" :valor="\App\Services\EstadisticaService::duracion($resumen['promedio_espera'])" icono="bi-stopwatch" color="info" />
        </div>
        <div class="col-6 col-lg-2">
            <x-stat-card titulo="Atención promedio" :valor="\App\Services\EstadisticaService::duracion($resumen['promedio_atencion'])" icono="bi-clock-history" color="secondary" />
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-5">
            <div class="card app-card h-100">
                <div class="card-header">
                    <h3 class="card-title">Por día</h3>
                    <p class="card-subtitle">Turnos generados y atendidos.</p>
                </div>
                <div class="table-responsive">
                    <table class="table app-table align-middle mb-0">
                        <thead>
                            <tr><th>Fecha</th><th class="text-center">Total</th><th class="text-center">Atendidos</th><th class="text-center">Efectividad</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($porDia as $fila)
                                <tr>
                                    <td>{{ \Illuminate\Support\Carbon::parse($fila->fecha)->format('d/m/Y') }}</td>
                                    <td class="text-center">{{ $fila->total }}</td>
                                    <td class="text-center">{{ $fila->atendidos }}</td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill text-bg-light">
                                            {{ $fila->total > 0 ? round($fila->atendidos / $fila->total * 100) : 0 }}%
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">Sin datos en el período.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card app-card h-100">
                <div class="card-header">
                    <h3 class="card-title">Por área</h3>
                    <p class="card-subtitle">Distribución de la demanda y la atención.</p>
                </div>
                <div class="table-responsive">
                    <table class="table app-table align-middle mb-0">
                        <thead>
                            <tr><th>Área</th><th class="text-center">Total</th><th class="text-center">En espera</th><th class="text-center">Atendidos</th><th class="text-center">No presentados</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($porArea as $fila)
                                <tr>
                                    <td><strong>{{ $fila->area->nombre }}</strong></td>
                                    <td class="text-center">{{ $fila->total }}</td>
                                    <td class="text-center">{{ $fila->pendientes }}</td>
                                    <td class="text-center">{{ $fila->atendidos }}</td>
                                    <td class="text-center">{{ $fila->no_presentados }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">Sin áreas registradas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card app-card mb-4">
        <div class="card-header">
            <h3 class="card-title">Por servicio</h3>
            <p class="card-subtitle">Servicios con mayor demanda en el período seleccionado.</p>
        </div>
        <div class="table-responsive">
            <table class="table app-table align-middle mb-0">
                <thead>
                    <tr><th>Servicio</th><th>Área</th><th class="text-center">Total</th><th class="text-center">Atendidos</th><th style="width: 30%">Efectividad</th></tr>
                </thead>
                <tbody>
                    @forelse ($porServicio as $fila)
                        <tr>
                            <td><strong>{{ $fila->servicio?->nombre ?? 'Servicio eliminado' }}</strong></td>
                            <td>{{ $fila->servicio?->area?->nombre ?? '—' }}</td>
                            <td class="text-center">{{ $fila->total }}</td>
                            <td class="text-center">{{ $fila->atendidos }}</td>
                            <td>
                                @php($porcentaje = $fila->total > 0 ? round($fila->atendidos / $fila->total * 100) : 0)
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 6px">
                                        <div class="progress-bar bg-success" style="width: {{ $porcentaje }}%"></div>
                                    </div>
                                    <span class="small text-muted">{{ $porcentaje }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Sin datos en el período.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card app-card">
        <div class="card-header">
            <h3 class="card-title">Detalle de turnos</h3>
            <p class="card-subtitle">{{ $turnos->total() }} turnos encontrados.</p>
        </div>
        <div class="table-responsive">
            <table class="table app-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Turno</th>
                        <th>Fecha</th>
                        <th>Servicio</th>
                        <th>Ciudadano</th>
                        <th>Ventanilla</th>
                        <th>Estado</th>
                        <th>Espera</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($turnos as $turno)
                        <tr>
                            <td><span class="ticket-mini">{{ $turno->codigo }}</span></td>
                            <td>{{ $turno->fecha->format('d/m/Y') }}</td>
                            <td>
                                <strong>{{ $turno->servicio?->nombre }}</strong>
                                <span class="d-block small text-muted">{{ $turno->area?->nombre }}</span>
                            </td>
                            <td>{{ $turno->ciudadano?->user?->name ?? '—' }}</td>
                            <td>{{ $turno->ventanilla?->codigo ?? '—' }}</td>
                            <td><x-estado-turno :estado="$turno->estado" /></td>
                            <td class="small text-muted">{{ \App\Services\EstadisticaService::duracion($turno->esperaSegundos()) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No hay turnos para los filtros seleccionados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($turnos->hasPages())
            <div class="card-footer bg-white">{{ $turnos->links() }}</div>
        @endif
    </div>
@endsection
