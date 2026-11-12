@extends('layouts.app')

@section('seccion', 'Áreas')
@section('titulo', 'Editar área')

@section('contenido')
    <div class="page-head">
        <div>
            <span class="page-kicker">Organización</span>
            <h2>{{ $area->nombre }}</h2>
            <p class="mb-0 text-muted">Actualiza la información del área de atención.</p>
        </div>
        <a href="{{ route('admin.areas.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
    </div>

    @include('admin.areas._form')
@endsection
