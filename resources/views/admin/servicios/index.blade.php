@extends('layouts.app')

@section('seccion', 'Configuración')
@section('titulo', 'Servicios')

@section('contenido')
    <div class="page-head">
        <div>
            <span class="page-kicker">Catálogo</span>
            <h2>Servicios disponibles</h2>
            <p class="mb-0 text-muted">Cada servicio pertenece a un área y genera su propia numeración de turnos.</p>
        </div>
        <a href="{{ route('admin.servicios.create') }}" class="btn btn-brand">
            <i class="bi bi-plus-lg me-1"></i> Nuevo servicio
        </a>
    </div>

    <div class="card app-card">
        <div class="card-header">
            <form method="GET" action="{{ route('admin.servicios.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="search" name="buscar" value="{{ $buscar }}" class="form-control" placeholder="Buscar servicio">
                    </div>
                </div>
                <div class="col-8 col-md-3">
                    <select name="area_id" class="form-select">
                        <option value="">Todas las áreas</option>
                        @foreach ($areas as $area)
                            <option value="{{ $area->id }}" @selected($areaId === $area->id)>{{ $area->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto"><button class="btn btn-outline-secondary">Filtrar</button></div>
                @if ($buscar !== '' || $areaId > 0)
                    <div class="col-auto"><a href="{{ route('admin.servicios.index') }}" class="btn btn-link">Limpiar</a></div>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table app-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Prefijo</th>
                        <th>Servicio</th>
                        <th>Área</th>
                        <th class="text-center">Turnos históricos</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($servicios as $servicio)
                        <tr>
                            <td><span class="prefix-badge">{{ $servicio->prefijo }}</span></td>
                            <td>
                                <strong>{{ $servicio->nombre }}</strong>
                                <span class="d-block small text-muted">{{ \Illuminate\Support\Str::limit($servicio->descripcion ?: 'Sin descripción', 70) }}</span>
                            </td>
                            <td>{{ $servicio->area?->nombre }}</td>
                            <td class="text-center">{{ $servicio->turnos_count }}</td>
                            <td>
                                <span class="badge rounded-pill {{ $servicio->activo ? 'text-bg-success' : 'text-bg-secondary' }}">
                                    {{ $servicio->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('admin.servicios.edit', $servicio) }}" class="btn btn-sm btn-outline-secondary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.servicios.destroy', $servicio) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar"
                                                data-confirm="¿Eliminar el servicio «{{ $servicio->nombre }}»?">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-5">No se encontraron servicios.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($servicios->hasPages())
            <div class="card-footer bg-white">{{ $servicios->links() }}</div>
        @endif
    </div>
@endsection
