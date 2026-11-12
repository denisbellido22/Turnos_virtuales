<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo', 'Panel') · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="app-body">
<a class="skip-link" href="#contenido">Saltar al contenido</a>

<div class="app-shell">
    <aside class="offcanvas-lg offcanvas-start app-sidebar" tabindex="-1" id="appSidebar" aria-labelledby="appSidebarLabel">
        <div class="offcanvas-header d-lg-none">
            <span class="sidebar-brand-text" id="appSidebarLabel">{{ config('app.name') }}</span>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#appSidebar" aria-label="Cerrar"></button>
        </div>

        <div class="sidebar-brand">
            <span class="brand-mark"><i class="bi bi-ticket-perforated-fill"></i></span>
            <a href="{{ auth()->user()->rutaPanel() }}" class="sidebar-brand-text">
                Turnos<span>Virtuales</span>
            </a>
        </div>

        <div class="sidebar-user">
            <span class="avatar avatar-sm">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
            <div class="min-w-0">
                <strong class="d-block text-truncate">{{ auth()->user()->name }}</strong>
                <span class="sidebar-role">{{ ucfirst(auth()->user()->rol?->nombre ?? 'usuario') }}</span>
            </div>
        </div>

        <nav class="sidebar-nav" aria-label="Navegación principal">
            <p class="sidebar-heading">Operación</p>

            @if (auth()->user()->esAdministrador())
                <a class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                    <i class="bi bi-grid-1x2"></i> Dashboard
                </a>
                <a class="sidebar-link {{ request()->routeIs('admin.usuarios.*') ? 'active' : '' }}" href="{{ route('admin.usuarios.index') }}">
                    <i class="bi bi-people"></i> Usuarios y empleados
                </a>
                <a class="sidebar-link {{ request()->routeIs('admin.areas.*') ? 'active' : '' }}" href="{{ route('admin.areas.index') }}">
                    <i class="bi bi-diagram-3"></i> Áreas de atención
                </a>
                <a class="sidebar-link {{ request()->routeIs('admin.servicios.*') ? 'active' : '' }}" href="{{ route('admin.servicios.index') }}">
                    <i class="bi bi-list-check"></i> Servicios
                </a>
                <a class="sidebar-link {{ request()->routeIs('admin.ventanillas.*') ? 'active' : '' }}" href="{{ route('admin.ventanillas.index') }}">
                    <i class="bi bi-window-stack"></i> Ventanillas
                </a>
                <a class="sidebar-link {{ request()->routeIs('admin.reportes.*') ? 'active' : '' }}" href="{{ route('admin.reportes.index') }}">
                    <i class="bi bi-bar-chart-line"></i> Estadísticas y reportes
                </a>
            @elseif (auth()->user()->esEmpleado())
                <a class="sidebar-link {{ request()->routeIs('atencion.panel') ? 'active' : '' }}" href="{{ route('atencion.panel') }}">
                    <i class="bi bi-headset"></i> Panel de atención
                </a>
            @else
                <a class="sidebar-link {{ request()->routeIs('ciudadano.turnos.*') ? 'active' : '' }}" href="{{ route('ciudadano.turnos.index') }}">
                    <i class="bi bi-ticket-perforated"></i> Mis turnos
                </a>
            @endif

            <p class="sidebar-heading mt-4">Sistema</p>
            <a class="sidebar-link" href="{{ route('pantalla.index') }}" target="_blank" rel="noopener">
                <i class="bi bi-display"></i> Pantalla pública
                <i class="bi bi-box-arrow-up-right ms-auto small"></i>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sidebar-link sidebar-button">
                    <i class="bi bi-box-arrow-left"></i> Cerrar sesión
                </button>
            </form>
        </nav>

        <div class="sidebar-footer">
            <span class="live-dot"></span>
            Sistema en línea
        </div>
    </aside>

    <div class="app-content">
        <header class="app-topbar">
            <button class="btn btn-icon d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#appSidebar" aria-controls="appSidebar" aria-label="Abrir menú">
                <i class="bi bi-list"></i>
            </button>

            <div class="topbar-title">
                <span class="topbar-eyebrow">@yield('seccion', 'Panel')</span>
                <h1>@yield('titulo', 'Panel')</h1>
            </div>

            <div class="topbar-actions">
                <span class="live-pill d-none d-md-inline-flex">
                    <span class="live-dot"></span> Tiempo real
                </span>
                <a href="{{ route('pantalla.index') }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary d-none d-sm-inline-flex">
                    <i class="bi bi-display"></i> Pantalla
                </a>

                <div class="dropdown">
                    <button class="user-chip" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="avatar avatar-sm">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                        <span class="d-none d-md-flex flex-column text-start">
                            <strong>{{ auth()->user()->name }}</strong>
                            <small>{{ ucfirst(auth()->user()->rol?->nombre ?? 'usuario') }}</small>
                        </span>
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                        <li><span class="dropdown-item-text small text-muted">{{ auth()->user()->email }}</span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-left me-2"></i>Cerrar sesión
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <main id="contenido" class="app-main">
            @include('partials.flash')
            @yield('contenido')
        </main>

        <footer class="app-footer">
            <span>{{ config('app.name') }} · Gestión de turnos en tiempo real</span>
            <span>{{ now()->translatedFormat('l, d \d\e F \d\e Y') }}</span>
        </footer>
    </div>
</div>
</body>
</html>
