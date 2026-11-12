@extends('layouts.app')

@section('seccion', 'Configuración')
@section('titulo', 'Ventanillas')

@section('contenido')
    <div class="page-head">
        <div>
            <span class="page-kicker">Puestos de atención</span>
            <h2>Ventanillas</h2>
            <p class="mb-0 text-muted">Configura los puestos físicos y su área de atención.</p>
        </div>
        <a href="{{ route('admin.ventanillas.create') }}" class="btn btn-brand">
            <i class="bi bi-plus-lg me-1"></i> Nueva ventanilla
        </a>
    </div>

    <div class="card app-card">
        <div class="card-header">
            <form method="GET" action="{{ route('admin.ventanillas.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="search" name="buscar" value="{{ $buscar }}" class="form-control" placeholder="Código o nombre">
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
                    <div class="col-auto"><a href="{{ route('admin.ventanillas.index') }}" class="btn btn-link">Limpiar</a></div>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table app-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Ventanilla</th>
                        <th>Área</th>
                        <th class="text-center">Empleados</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ventanillas as $ventanilla)
                        <tr>
                            <td><span class="window-code">{{ $ventanilla->codigo }}</span></td>
                            <td><strong>{{ $ventanilla->nombre }}</strong></td>
                            <td>{{ $ventanilla->area?->nombre }}</td>
                            <td class="text-center">{{ $ventanilla->users_count }}</td>
                            <td>
                                <span class="badge rounded-pill {{ $ventanilla->activo ? 'text-bg-success' : 'text-bg-secondary' }}">
                                    {{ $ventanilla->activo ? 'Activa' : 'Inactiva' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('admin.ventanillas.edit', $ventanilla) }}" class="btn btn-sm btn-outline-secondary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.ventanillas.destroy', $ventanilla) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar"
                                                data-confirm="¿Eliminar la ventanilla {{ $ventanilla->codigo }}?">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-5">No se encontraron ventanillas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($ventanillas->hasPages())
            <div class="card-footer bg-white">{{ $ventanillas->links() }}</div>
        @endif
    </div>
@endsection
