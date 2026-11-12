<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} · Turnos virtuales y gestión de colas</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="landing-body">
<a class="skip-link" href="#contenido">Saltar al contenido</a>

<header class="landing-header">
    <div class="container d-flex align-items-center justify-content-between gap-3">
        <a href="{{ route('home') }}" class="landing-brand">
            <span class="brand-mark"><i class="bi bi-ticket-perforated-fill"></i></span>
            <span>Turnos<span>Virtuales</span></span>
        </a>

        <nav class="landing-nav d-none d-md-flex">
            <a href="#servicios">Servicios</a>
            <a href="#como-funciona">Cómo funciona</a>
            <a href="{{ route('pantalla.index') }}" target="_blank" rel="noopener">Pantalla pública</a>
        </nav>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-secondary">Iniciar sesión</a>
            <a href="{{ route('turnos.create') }}" class="btn btn-sm btn-brand">Solicitar turno</a>
        </div>
    </div>
</header>

<main id="contenido">
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <span class="badge-soft mb-3"><span class="live-dot"></span> Atención sin filas</span>
                    <h1 class="hero-title">Tu turno, sin esperar de pie.</h1>
                    <p class="hero-lead">
                        Solicita tu turno en línea, consulta tu posición en la cola y recibe el llamado
                        en tiempo real. Un sistema pensado para ordenar la atención y respetar tu tiempo.
                    </p>
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <a href="{{ route('turnos.create') }}" class="btn btn-brand btn-lg">
                            <i class="bi bi-ticket-perforated me-2"></i>Obtener mi turno
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-lg">Ya tengo cuenta</a>
                    </div>
                    <div class="hero-points">
                        <span><i class="bi bi-check2-circle"></i> Turnos consecutivos</span>
                        <span><i class="bi bi-check2-circle"></i> Seguimiento en vivo</span>
                        <span><i class="bi bi-check2-circle"></i> Múltiples áreas</span>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="hero-panel">
                        <div class="hero-panel-top">
                            <span class="hero-panel-label">Vista de ejemplo</span>
                            <span class="live-pill live-pill-dark"><i class="bi bi-phone"></i> Interfaz adaptable</span>
                        </div>
                        <div class="hero-ticket">
                            <span class="hero-ticket-label">EJEMPLO DE TURNO</span>
                            <div class="hero-ticket-code">A-014</div>
                            <span class="hero-ticket-note">El número se asigna al solicitar el servicio</span>
                        </div>
                        <div class="hero-panel-bottom">
                            <div>
                                <span class="hero-meta-label">Ventanilla</span>
                                <strong>V-01</strong>
                            </div>
                            <div>
                                <span class="hero-meta-label">Estado</span>
                                <strong class="text-warning">Llamando</strong>
                            </div>
                            <div>
                                <span class="hero-meta-label">Posición</span>
                                <strong>1.º</strong>
                            </div>
                        </div>
                    </div>
                    <p class="hero-caption"><i class="bi bi-info-circle me-1"></i>La pantalla pública se actualiza al instante, sin recargar la página.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="landing-section" id="servicios">
        <div class="container">
            <div class="section-heading">
                <span class="eyebrow">Servicios disponibles</span>
                <h2>Elige el área que necesitas</h2>
                <p>Cada área organiza su propia cola para que la atención sea ordenada y transparente.</p>
            </div>

            @if ($areas->isEmpty())
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <h3>Aún no hay servicios publicados</h3>
                    <p>El administrador debe registrar las áreas y servicios de atención.</p>
                </div>
            @else
                <div class="row g-4">
                    @foreach ($areas as $area)
                        <div class="col-md-6 col-lg-4">
                            <article class="area-card h-100">
                                <div class="area-card-icon"><i class="bi bi-building"></i></div>
                                <h3>{{ $area->nombre }}</h3>
                                <p>{{ $area->descripcion ?: 'Atención presencial organizada por turnos.' }}</p>
                                <ul class="service-list">
                                    @forelse ($area->servicios as $servicio)
                                        <li><i class="bi bi-check2"></i> {{ $servicio->nombre }}</li>
                                    @empty
                                        <li class="text-muted">Sin servicios activos.</li>
                                    @endforelse
                                </ul>
                                <a href="{{ route('turnos.create') }}" class="area-link">Solicitar turno <i class="bi bi-arrow-right"></i></a>
                            </article>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="landing-section landing-section-muted" id="como-funciona">
        <div class="container">
            <div class="section-heading">
                <span class="eyebrow">Cómo funciona</span>
                <h2>Cuatro pasos, cero complicaciones</h2>
            </div>
            <div class="row g-4">
                @foreach ([
                    ['icon' => 'bi-person-plus', 'title' => 'Escribe tus datos', 'text' => 'Ingresa tu nombre y documento; no necesitas crear una cuenta.'],
                    ['icon' => 'bi-list-check', 'title' => 'Elige el servicio', 'text' => 'Selecciona el área y el trámite que necesitas realizar.'],
                    ['icon' => 'bi-ticket-perforated', 'title' => 'Recibe tu turno', 'text' => 'El sistema genera un número único y consecutivo para hoy.'],
                    ['icon' => 'bi-broadcast', 'title' => 'Sigue tu llamado', 'text' => 'Consulta tu posición y acércate cuando la pantalla te llame.'],
                ] as $paso)
                    <div class="col-sm-6 col-lg-3">
                        <div class="step-card h-100">
                            <span class="step-number">{{ $loop->iteration }}</span>
                            <i class="bi {{ $paso['icon'] }}"></i>
                            <h3>{{ $paso['title'] }}</h3>
                            <p>{{ $paso['text'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="landing-cta">
        <div class="container d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4">
            <div>
                <h2>¿Ya tienes un turno?</h2>
                <p>Consulta tu posición en la cola y el estado de tu atención en tiempo real.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <form method="GET" action="{{ route('turnos.buscar') }}" class="d-flex gap-2">
                    <input type="text" name="codigo" class="form-control form-control-lg" placeholder="Ej. GEN-014"
                           aria-label="Código de turno" maxlength="20" required>
                    <button type="submit" class="btn btn-light btn-lg text-nowrap">Consultar mi turno</button>
                </form>
                <a href="{{ route('pantalla.index') }}" target="_blank" rel="noopener" class="btn btn-outline-light btn-lg">
                    <i class="bi bi-display me-2"></i>Pantalla de turnos
                </a>
            </div>
        </div>
    </section>
</main>

<footer class="landing-footer">
    <div class="container d-flex flex-column flex-sm-row justify-content-between gap-2">
        <span>{{ config('app.name') }} · Sistema de turnos virtuales y gestión de colas</span>
        <span>Actualización en tiempo real con WebSockets</span>
    </div>
</footer>
</body>
</html>
