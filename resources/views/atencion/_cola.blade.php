@if ($ventanilla)
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <x-stat-card titulo="En espera" :valor="$totalPendientes" icono="bi-hourglass-split" color="warning"
                         :detalle="'Área: '.$ventanilla->area?->nombre" />
        </div>
        <div class="col-6 col-xl-3">
            <x-stat-card titulo="Atendidos hoy" :valor="$atendidosHoy" icono="bi-check2-circle" color="success"
                         detalle="En tu ventanilla" />
        </div>
        <div class="col-6 col-xl-3">
            <x-stat-card titulo="Ausentes hoy" :valor="$ausentesHoy" icono="bi-person-x" color="secondary"
                         detalle="Llamados sin respuesta" />
        </div>
        <div class="col-6 col-xl-3">
            <x-stat-card titulo="Atención promedio" :valor="\App\Services\EstadisticaService::duracion($promedioAtencion)"
                         icono="bi-stopwatch" color="info" detalle="Desde el llamado" />
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="call-card {{ $activo ? 'call-card-active' : '' }}">
                @if ($activo)
                    <div class="call-card-header">
                        <span class="live-pill live-pill-light"><span class="live-dot"></span> Llamando ahora</span>
                        <span class="call-time"><i class="bi bi-clock me-1"></i>{{ $activo->llamado_at?->format('H:i') }}</span>
                    </div>

                    <div class="call-code">{{ $activo->codigo }}</div>
                    <p class="call-service">{{ $activo->servicio?->nombre }} · {{ $activo->area?->nombre }}</p>

                    <div class="call-citizen">
                        <i class="bi bi-person-circle"></i>
                        <span>{{ $activo->ciudadano?->user?->name ?? 'Ciudadano' }}</span>
                    </div>

                    <div class="call-actions">
                        <form method="POST" action="{{ route('atencion.turnos.repetir', $activo) }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-light">
                                <i class="bi bi-arrow-repeat me-1"></i> Repetir llamado
                            </button>
                        </form>
                        <form method="POST" action="{{ route('atencion.turnos.atender', $activo) }}">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check2-circle me-1"></i> Marcar atendido
                            </button>
                        </form>
                        <form method="POST" action="{{ route('atencion.turnos.atender_audio', $activo) }}">
                            @csrf
                            <button type="submit" class="btn btn-info"
                                    data-tooltip="tooltip" title="Atender mediante audio">
                                <i class="bi bi-microphone me-1"></i> Atender audio
                            </button>
                        </form>
                        <form method="POST" action="{{ route('atencion.turnos.llamar_audio', $activo) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary"
                                    data-tooltip="tooltip" title="Llamar mediante audio">
                                <i class="bi bi-telephone me-1"></i> Llamar audio
                            </button>
                        </form>
                        <form method="POST" action="{{ route('atencion.turnos.repetir_audio', $activo) }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary"
                                    data-tooltip="tooltip" title="Repetir llamado mediante audio">
                                <i class="bi bi-arrow-repeat me-1"></i> Repetir audio
                            </button>
                        </form>
                        <form method="POST" action="{{ route('atencion.turnos.ausente', $activo) }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-light"
                                    data-confirm="¿Marcar el turno {{ $activo->codigo }} como ausente?">
                                <i class="bi bi-person-x me-1"></i> Ausente
                            </button>
                        </form>
                    </div>
                @else
                    <div class="call-card-empty">
                        <span class="call-empty-icon"><i class="bi bi-megaphone"></i></span>
                        <h3>Sin llamado activo</h3>
                        <p>Llama al siguiente turno en espera de tu área.</p>
                        <form method="POST" action="{{ route('atencion.turnos.llamar') }}">
                            @csrf
                            <button type="submit" class="btn btn-light btn-lg" @disabled($totalPendientes === 0)>
                                <i class="bi bi-megaphone me-2"></i> Llamar siguiente
                            </button>
                        </form>
                        @if ($totalPendientes === 0)
                            <p class="call-empty-note">No hay turnos pendientes en este momento.</p>
                        @endif
                    </div>
                @endif
            </div>

            @if ($activo)
                <div class="call-hint">
                    <i class="bi bi-info-circle"></i>
                    Finaliza el llamado actual para poder llamar al siguiente turno.
                </div>
            @endif
        </div>

        <div class="col-lg-5">
            <div class="card app-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="card-title">Cola del área</h3>
                        <p class="card-subtitle">Orden de llegada.</p>
                    </div>
                    <span class="badge rounded-pill text-bg-warning">{{ $totalPendientes }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table app-table align-middle mb-0">
                        <thead>
                            <tr><th>#</th><th>Turno</th><th>Servicio</th><th>Espera</th><th></th></tr>
                        </thead>
                        <tbody>
                            @forelse ($pendientes as $turno)
                                <tr>
                                    <td class="text-muted">{{ $loop->iteration }}</td>
                                    <td>
                                        <span class="ticket-mini">{{ $turno->codigo }}</span>
                                        <span class="d-block small text-muted">{{ $turno->ciudadano?->user?->name }}</span>
                                    </td>
                                    <td class="small">{{ $turno->servicio?->nombre }}</td>
                                    <td class="small text-muted">
                                        @php($minutos = (int) $turno->created_at->diffInMinutes(now()))
                                        {{ $minutos < 1 ? '< 1 min' : $minutos.' min' }}
                                    </td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('atencion.turnos.cancelar', $turno) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Cancelar turno"
                                                    data-confirm="¿Cancelar el turno {{ $turno->codigo }}?">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">La cola está vacía.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($totalPendientes > $pendientes->count())
                    <div class="card-footer bg-white small text-muted">
                        Mostrando {{ $pendientes->count() }} de {{ $totalPendientes }} turnos en espera.
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif
