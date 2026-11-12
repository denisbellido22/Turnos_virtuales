<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo', 'Acceso') · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="guest-body">
<a class="skip-link" href="#contenido">Saltar al contenido</a>

<div class="guest-shell">
    <aside class="guest-aside">
        <a href="{{ route('home') }}" class="guest-brand">
            <span class="brand-mark"><i class="bi bi-ticket-perforated-fill"></i></span>
            <span>Turnos<span>Virtuales</span></span>
        </a>

        <div class="guest-aside-content">
            <span class="badge-soft-light mb-3">Atención organizada</span>
            <h2>Tu tiempo importa.<br>Tu turno también.</h2>
            <p>Solicita tu turno en línea, sigue tu posición en la cola y acércate justo cuando te llamen.</p>

            <ul class="guest-features">
                <li><i class="bi bi-broadcast"></i> Actualizaciones en tiempo real</li>
                <li><i class="bi bi-list-ol"></i> Cola ordenada por área y servicio</li>
                <li><i class="bi bi-phone"></i> Disponible en computadora, tablet y celular</li>
            </ul>
        </div>

        <a href="{{ route('pantalla.index') }}" class="guest-screen-link" target="_blank" rel="noopener">
            <i class="bi bi-display"></i> Ver pantalla pública de turnos
        </a>
    </aside>

    <main class="guest-main" id="contenido">
        <div class="guest-mobile-brand">
            <span class="brand-mark"><i class="bi bi-ticket-perforated-fill"></i></span>
            <span>Turnos<span>Virtuales</span></span>
        </div>

        <div class="auth-card">
            @yield('contenido')
        </div>

        <p class="guest-footer">
            <a href="{{ route('home') }}"><i class="bi bi-arrow-left"></i> Volver al inicio</a>
            <span>·</span>
            <a href="{{ route('pantalla.index') }}" target="_blank" rel="noopener">Pantalla pública</a>
        </p>
    </main>
</div>
</body>
</html>
