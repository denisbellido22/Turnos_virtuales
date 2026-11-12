@extends('layouts.app')

@section('seccion', 'Ventanillas')
@section('titulo', 'Nueva ventanilla')

@section('contenido')
    <div class="page-head">
        <div>
            <span class="page-kicker">Puestos de atención</span>
            <h2>Nueva ventanilla</h2>
            <p class="mb-0 text-muted">Registra un puesto físico para un área de atención.</p>
        </div>
        <a href="{{ route('admin.ventanillas.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
    </div>

    @include('admin.ventanillas._form')
@endsection
