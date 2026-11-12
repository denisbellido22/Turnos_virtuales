@extends('layouts.app')

@section('seccion', 'Usuarios')
@section('titulo', 'Nuevo usuario')

@section('contenido')
    <div class="page-head">
        <div>
            <span class="page-kicker">Equipo de trabajo</span>
            <h2>Nuevo usuario</h2>
            <p class="mb-0 text-muted">Crea una cuenta de administrador o empleado.</p>
        </div>
        <a href="{{ route('admin.usuarios.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>

    @include('admin.usuarios._form')
@endsection
