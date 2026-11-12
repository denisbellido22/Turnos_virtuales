@extends('layouts.guest')

@section('titulo', 'Iniciar sesión')

@section('contenido')
    <div class="auth-header">
        <span class="eyebrow">Bienvenido de nuevo</span>
        <h1>Iniciar sesión</h1>
        <p>Accede a tu panel para continuar con la atención.</p>
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

    @if (session('success'))
        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" class="auth-form" novalidate>
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Correo electrónico</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                       class="form-control @error('email') is-invalid @enderror" placeholder="nombre@correo.com">
            </div>
            @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                       class="form-control @error('password') is-invalid @enderror" placeholder="Tu contraseña">
            </div>
            @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1" @checked(old('remember'))>
                <label class="form-check-label" for="remember">Recordarme</label>
            </div>
            <a href="{{ route('register') }}" class="small-link">Crear cuenta</a>
        </div>

        <button type="submit" class="btn btn-brand w-100 btn-lg">
            Entrar <i class="bi bi-arrow-right ms-1"></i>
        </button>
    </form>

    <p class="auth-switch">
        ¿Aún no tienes cuenta? <a href="{{ route('register') }}">Regístrate como ciudadano</a>
    </p>

    @if (app()->environment('local'))
        <div class="demo-accounts">
            <span class="demo-title"><i class="bi bi-key"></i> Cuentas de demostración</span>
            <ul>
                <li><strong>Administrador:</strong> admin@turnos.test · Admin123!</li>
                <li><strong>Empleado:</strong> maria.lopez@turnos.test · Empleado123!</li>
                <li><strong>Ciudadano:</strong> ciudadano@turnos.test · Ciudadano123!</li>
            </ul>
        </div>
    @endif
@endsection
