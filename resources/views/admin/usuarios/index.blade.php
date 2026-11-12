@extends('layouts.app')

@section('seccion', 'Administración')
@section('titulo', 'Usuarios y empleados')

@section('contenido')
    <div class="page-head">
        <div>
            <span class="page-kicker">Equipo de trabajo</span>
            <h2>Usuarios y empleados</h2>
            <p class="mb-0 text-muted">Administra los accesos del personal y su puesto de atención.</p>
        </div>
        <a href="{{ route('admin.usuarios.create') }}" class="btn btn-brand">
            <i class="bi bi-person-plus me-1"></i> Nuevo usuario
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3"><x-stat-card titulo="Administradores" :valor="$totalAdministradores" icono="bi-shield-check" color="primary" /></div>
        <div class="col-6 col-lg-3"><x-stat-card titulo="Empleados" :valor="$totalEmpleados" icono="bi-headset" color="info" /></div>
        <div class="col-6 col-lg-3"><x-stat-card titulo="Cuentas activas" :valor="$totalActivos" icono="bi-person-check" color="success" /></div>
        <div class="col-6 col-lg-3"><x-stat-card titulo="Ciudadanos" :valor="\App\Models\Ciudadano::count()" icono="bi-people" color="warning" /></div>
    </div>

    <div class="card app-card">
        <div class="card-header">
            <form method="GET" action="{{ route('admin.usuarios.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="search" name="buscar" value="{{ $buscar }}" class="form-control" placeholder="Buscar por nombre o correo">
                    </div>
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-secondary" type="submit">Buscar</button>
                </div>
                @if ($buscar !== '')
                    <div class="col-auto"><a href="{{ route('admin.usuarios.index') }}" class="btn btn-link">Limpiar</a></div>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table app-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Área</th>
                        <th>Ventanilla</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($usuarios as $usuario)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar avatar-sm">{{ strtoupper(substr($usuario->name, 0, 1)) }}</span>
                                    <div>
                                        <strong>{{ $usuario->name }}</strong>
                                        <span class="d-block small text-muted">{{ $usuario->email }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge rounded-pill {{ $usuario->esAdministrador() ? 'text-bg-primary' : 'text-bg-info' }}">
                                    {{ ucfirst($usuario->rol?->nombre) }}
                                </span>
                            </td>
                            <td>{{ $usuario->area?->nombre ?? '—' }}</td>
                            <td>{{ $usuario->ventanilla?->codigo ?? '—' }}</td>
                            <td>
                                <span class="badge rounded-pill {{ $usuario->activo ? 'text-bg-success' : 'text-bg-secondary' }}">
                                    {{ $usuario->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('admin.usuarios.edit', $usuario) }}" class="btn btn-sm btn-outline-secondary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if (! $usuario->is(auth()->user()))
                                        <form method="POST" action="{{ route('admin.usuarios.estado', $usuario) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm {{ $usuario->activo ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                                    data-confirm="{{ $usuario->activo ? '¿Desactivar este usuario?' : '¿Activar este usuario?' }}"
                                                    title="{{ $usuario->activo ? 'Desactivar' : 'Activar' }}">
                                                <i class="bi {{ $usuario->activo ? 'bi-person-dash' : 'bi-person-check' }}"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-5">No se encontraron usuarios.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($usuarios->hasPages())
            <div class="card-footer bg-white">
                {{ $usuarios->links() }}
            </div>
        @endif
    </div>
@endsection
