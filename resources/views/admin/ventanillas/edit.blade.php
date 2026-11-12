@extends('layouts.app')

@section('seccion', 'Ventanillas')
@section('titulo', 'Editar ventanilla')

@section('contenido')
    <div class="page-head">
        <div>
            <span class="page-kicker">Puestos de atención</span>
            <h2>{{ $ventanilla->codigo }} · {{ $ventanilla->nombre }}</h2>
            <p class="mb-0 text-muted">Actualiza la información del puesto de atención.</p>
        </div>
        <a href="{{ route('admin.ventanillas.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
    </div>

    @include('admin.ventanillas._form')
@endsection
