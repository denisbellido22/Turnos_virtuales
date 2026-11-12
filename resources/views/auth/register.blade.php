@extends('layouts.guest')

@section('titulo', 'Crear cuenta')

@section('contenido')
    <div class="auth-header">
        <span class="eyebrow">Registro de ciudadanos</span>
        <h1>Crea tu cuenta</h1>
        <p>Con tu cuenta podrás solicitar turnos y seguir tu atención en tiempo real.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register.store') }}" class="auth-form" novalidate>
        @csrf

        <div class="row g-3">
            <div class="col-12">
                <label for="name" class="form-label">Nombre completo</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                       class="form-control @error('name') is-invalid @enderror" placeholder="Nombres y apellidos">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label for="email" class="form-label">Correo electrónico</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                       class="form-control @error('email') is-invalid @enderror" placeholder="nombre@correo.com">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label for="documento" class="form-label">Documento de identidad</label>
                <input id="documento" type="text" name="documento" value="{{ old('documento') }}" required
                       class="form-control @error('documento') is-invalid @enderror" placeholder="Ej. 12345678">
                @error('documento')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label for="telefono" class="form-label">Teléfono <span class="text-muted fw-normal">(opcional)</span></label>
                <input id="telefono" type="text" name="telefono" value="{{ old('telefono') }}"
                       class="form-control @error('telefono') is-invalid @enderror" placeholder="Ej. 70000000">
                @error('telefono')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label for="direccion" class="form-label">Dirección <span class="text-muted fw-normal">(opcional)</span></label>
                <input id="direccion" type="text" name="direccion" value="{{ old('direccion') }}"
                       class="form-control @error('direccion') is-invalid @enderror" placeholder="Calle, número, zona">
                @error('direccion')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label for="password" class="form-label">Contraseña</label>
                <input id="password" type="password" name="password" required autocomplete="new-password"
                       class="form-control @error('password') is-invalid @enderror" placeholder="Mínimo 8 caracteres">
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                       class="form-control" placeholder="Repite tu contraseña">
            </div>
        </div>

        <button type="submit" class="btn btn-brand w-100 btn-lg mt-4">
            Crear mi cuenta <i class="bi bi-arrow-right ms-1"></i>
        </button>
    </form>

    <p class="auth-switch">
        ¿Ya tienes una cuenta? <a href="{{ route('login') }}">Inicia sesión</a>
    </p>
@endsection
