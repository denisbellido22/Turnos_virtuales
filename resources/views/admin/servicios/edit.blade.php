@extends('layouts.app')

@section('seccion', 'Servicios')
@section('titulo', 'Editar servicio')

@section('contenido')
    <div class="page-head">
        <div>
            <span class="page-kicker">Catálogo</span>
            <h2>{{ $servicio->nombre }}</h2>
            <p class="mb-0 text-muted">Actualiza la configuración del servicio.</p>
        </div>
        <a href="{{ route('admin.servicios.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
    </div>

    @include('admin.servicios._form')
@endsection
