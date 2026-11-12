@extends('layouts.app')

@section('seccion', 'Ciudadano')
@section('titulo', 'Mis turnos')

@section('contenido')
    <div class="page-head">
        <div>
            <span class="page-kicker">Hola, {{ strtok($ciudadano->user->name, ' ') }}</span>
            <h2>Mis turnos</h2>
            <p class="mb-0 text-muted">Solicita un turno y sigue tu posición en la cola en tiempo real.</p>
        </div>
        <a href="{{ route('pantalla.index') }}" target="_blank" rel="noopener" class="btn btn-outline-secondary">
            <i class="bi bi-display me-1"></i> Ver pantalla pública
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            @forelse ($activos as $turno)
                <div class="ticket-card mb-3 {{ $turno->estado === \App\Models\Turno::ESTADO_LLAMANDO ? 'ticket-card-calling' : '' }}">
                    <div class="ticket-card-top">
                        <div>
                            <span class="eyebrow">{{ $turno->area?->nombre }}</span>
                            <h3>{{ $turno->servicio?->nombre }}</h3>
                        </div>
                        <x-estado-turno :estado="$turno->estado" />
                    </div>

                    <div class="d-flex flex-column flex-sm-row align-items-sm-end justify-content-between gap-3">
                        <div>
                            <span class="ticket-label">Tu número</span>
                            <div class="ticket-code">{{ $turno->codigo }}</div>
                        </div>
                        <div class="ticket-position">
                            <span class="ticket-label">Posición en la cola</span>
                            <strong>
                                @if ($posiciones[$turno->id])
                                    {{ $posiciones[$turno->id] }}.º
                                @else
                                    —
                                @endif
                            </strong>
                        </div>
                    </div>

                    <div class="ticket-card-bottom">
                        <span><i class="bi bi-clock me-1"></i> Solicitado {{ $turno->created_at->format('H:i') }}</span>
                        <span><i class="bi bi-window-stack me-1"></i> {{ $turno->ventanilla?->codigo ?? 'Ventanilla por asignar' }}</span>
                        <a href="{{ route('ciudadano.turnos.show', $turno) }}" class="ticket-link">
                            Seguir en vivo <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            @empty
                <div class="card app-card mb-3">
                    <div class="card-body text-center py-5">
                        <span class="empty-icon"><i class="bi bi-ticket-perforated"></i></span>
                        <h3 class="h5 mt-3">No tienes turnos activos</h3>
                        <p class="text-muted mb-0">Selecciona un servicio para generar tu turno de hoy.</p>
                    </div>
                </div>
            @endforelse

            <div class="card app-card">
                <div class="card-header">
                    <h3 class="card-title">Solicitar un turno</h3>
                    <p class="card-subtitle">Selecciona el servicio que necesitas atender hoy.</p>
                </div>
                <div class="card-body">
                    @if ($servicios->isEmpty())
                        <div class="empty-state compact">
                            <i class="bi bi-inbox"></i>
                            <p>No hay servicios disponibles en este momento.</p>
                        </div>
                    @else
                        <form method="POST" action="{{ route('ciudadano.turnos.store') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="servicio_id" class="form-label">Servicio</label>
                                <select id="servicio_id" name="servicio_id" required
                                        class="form-select form-select-lg @error('servicio_id') is-invalid @enderror"
                                        @disabled($activos->isNotEmpty())>
                                    <option value="">Selecciona un servicio…</option>
                                    @foreach ($servicios as $areaNombre => $items)
                                        <optgroup label="{{ $areaNombre }}">
                                            @foreach ($items as $servicio)
                                                <option value="{{ $servicio->id }}" @selected((string) old('servicio_id') === (string) $servicio->id)>
                                                    {{ $servicio->nombre }} · {{ $areaNombre }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                @error('servicio_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                @if ($activos->isNotEmpty())
                                    <div class="form-text">Ya tienes un turno activo. Podrás solicitar otro cuando sea atendido o finalizado.</div>
                                @endif
                            </div>

                            <button type="submit" class="btn btn-brand btn-lg w-100" @disabled($activos->isNotEmpty())>
                                <i class="bi bi-ticket-perforated me-2"></i> Generar mi turno
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card app-card side-info-card mb-4">
                <div class="card-body">
                    <span class="side-icon"><i class="bi bi-broadcast"></i></span>
                    <h3>Seguimiento en vivo</h3>
                    <p>Tu pantalla de turno se actualiza automáticamente cuando cambia tu estado o posición. No necesitas recargar.</p>
                    <ul class="check-list">
                        <li><i class="bi bi-check2"></i> Conserva tu número de turno.</li>
                        <li><i class="bi bi-check2"></i> Atento a la pantalla pública y a tu teléfono.</li>
                        <li><i class="bi bi-check2"></i> Acércate a la ventanilla cuando te llamen.</li>
                    </ul>
                </div>
            </div>

            <div class="card app-card">
                <div class="card-body">
                    <h3 class="h6 text-uppercase text-muted letter-spacing mb-3">Tu cuenta</h3>
                    <dl class="account-data mb-0">
                        <div><dt>Nombre</dt><dd>{{ $ciudadano->user->name }}</dd></div>
                        <div><dt>Documento</dt><dd>{{ $ciudadano->documento }}</dd></div>
                        <div><dt>Correo</dt><dd>{{ $ciudadano->user->email }}</dd></div>
                        <div><dt>Teléfono</dt><dd>{{ $ciudadano->telefono ?: 'No registrado' }}</dd></div>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="card app-card mt-4">
        <div class="card-header">
            <h3 class="card-title">Historial reciente</h3>
            <p class="card-subtitle">Tus últimos turnos finalizados.</p>
        </div>
        <div class="table-responsive">
            <table class="table app-table align-middle mb-0">
                <thead>
                    <tr><th>Turno</th><th>Fecha</th><th>Servicio</th><th>Área</th><th>Ventanilla</th><th>Estado</th></tr>
                </thead>
                <tbody>
                    @forelse ($historial as $turno)
                        <tr>
                            <td><span class="ticket-mini">{{ $turno->codigo }}</span></td>
                            <td>{{ $turno->fecha->format('d/m/Y') }}</td>
                            <td>{{ $turno->servicio?->nombre }}</td>
                            <td>{{ $turno->area?->nombre }}</td>
                            <td>{{ $turno->ventanilla?->codigo ?? '—' }}</td>
                            <td><x-estado-turno :estado="$turno->estado" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Aún no tienes turnos finalizados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
