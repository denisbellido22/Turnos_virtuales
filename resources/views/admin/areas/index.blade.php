@extends('layouts.app')

@section('seccion', 'Configuración')
@section('titulo', 'Áreas de atención')

@section('contenido')
    <div class="page-head">
        <div>
            <span class="page-kicker">Organización</span>
            <h2>Áreas de atención</h2>
            <p class="mb-0 text-muted">Define las unidades que reciben y atienden ciudadanos.</p>
        </div>
        <a href="{{ route('admin.areas.create') }}" class="btn btn-brand">
            <i class="bi bi-plus-lg me-1"></i> Nueva área
        </a>
    </div>

    <div class="card app-card">
        <div class="card-header">
            <form method="GET" action="{{ route('admin.areas.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="search" name="buscar" value="{{ $buscar }}" class="form-control" placeholder="Buscar área">
                    </div>
                </div>
                <div class="col-auto"><button class="btn btn-outline-secondary">Buscar</button></div>
                @if ($buscar !== '')
                    <div class="col-auto"><a href="{{ route('admin.areas.index') }}" class="btn btn-link">Limpiar</a></div>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table app-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Área</th>
                        <th class="text-center">Servicios</th>
                        <th class="text-center">Ventanillas</th>
                        <th class="text-center">Personal</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($areas as $area)
                        <tr>
                            <td>
                                <strong>{{ $area->nombre }}</strong>
                                <span class="d-block small text-muted">{{ \Illuminate\Support\Str::limit($area->descripcion ?: 'Sin descripción', 80) }}</span>
                            </td>
                            <td class="text-center">{{ $area->servicios_count }}</td>
                            <td class="text-center">{{ $area->ventanillas_count }}</td>
                            <td class="text-center">{{ $area->users_count }}</td>
                            <td>
                                <span class="badge rounded-pill {{ $area->activo ? 'text-bg-success' : 'text-bg-secondary' }}">
                                    {{ $area->activo ? 'Activa' : 'Inactiva' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('admin.areas.edit', $area) }}" class="btn btn-sm btn-outline-secondary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.areas.destroy', $area) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar"
                                                data-confirm="¿Eliminar el área «{{ $area->nombre }} »? Esta acción no se puede deshacer.">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-5">No hay áreas registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($areas->hasPages())
            <div class="card-footer bg-white">{{ $areas->links() }}</div>
        @endif
    </div>
@endsection
