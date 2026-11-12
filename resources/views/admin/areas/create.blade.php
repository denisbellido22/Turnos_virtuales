@extends('layouts.app')

@section('seccion', 'Áreas')
@section('titulo', 'Nueva área')

@section('contenido')
    <div class="page-head">
        <div>
            <span class="page-kicker">Organización</span>
            <h2>Nueva área de atención</h2>
            <p class="mb-0 text-muted">Agrupa servicios y ventanillas bajo una misma unidad.</p>
        </div>
        <a href="{{ route('admin.areas.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
    </div>

    @include('admin.areas._form')
@endsection
