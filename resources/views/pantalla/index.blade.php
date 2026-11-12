<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Turnos en vivo · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="screen-body">
<div id="pantalla-turnos" class="screen-shell" data-datos-url="{{ route('pantalla.datos') }}">
    <header class="screen-header">
        <div class="screen-brand">
            <span class="brand-mark"><i class="bi bi-ticket-perforated-fill"></i></span>
            <div>
                <strong>{{ config('app.name') }}</strong>
                <span>Pantalla pública de turnos</span>
            </div>
        </div>

        <div class="screen-header-right">
            <span class="screen-live" id="conexion-estado">
                <span class="live-dot"></span> En vivo
            </span>
            <div class="screen-clock">
                <span id="reloj-hora">--:--</span>
                <small id="reloj-fecha">{{ now()->translatedFormat('l, d \d\e F') }}</small>
            </div>
        </div>
    </header>

    <main class="screen-main">
        <section class="screen-now" aria-live="polite">
            <div class="screen-now-heading">
                <span class="screen-kicker">Ahora atendiendo</span>
                <span class="screen-pending"><i class="bi bi-people"></i> <span id="pendientes-valor">{{ $pendientes }}</span> en espera</span>
            </div>

            <div id="pantalla-ahora" class="screen-now-card {{ $ultimo ? '' : 'is-empty' }}">
                @if ($ultimo)
                    <div class="screen-ticket-side">
                        <span class="screen-label">Turno</span>
                        <div id="turno-actual-codigo" class="screen-ticket-code">{{ $ultimo->codigo }}</div>
                        <div id="turno-actual-servicio" class="screen-service">{{ $ultimo->servicio?->nombre }}</div>
                        <div id="turno-actual-area" class="screen-area">{{ $ultimo->area?->nombre }}</div>
                    </div>

                    <div class="screen-window-side">
                        <span class="screen-label">Ventanilla</span>
                        <div id="turno-actual-ventanilla" class="screen-window-code">{{ $ultimo->ventanilla?->codigo ?? '—' }}</div>
                        <span id="turno-actual-hora" class="screen-called-at">
                            {{ $ultimo->llamado_at?->format('H:i') }}
                        </span>
                    </div>
                @else
                    <div class="screen-empty">
                        <i class="bi bi-megaphone"></i>
                        <h2>Esperando el primer llamado</h2>
                        <p>Los turnos llamados aparecerán aquí automáticamente.</p>
                    </div>
                @endif
            </div>
        </section>

        <section class="screen-history">
            <div class="screen-history-heading">
                <h2>Últimos llamados</h2>
                <span>Actualización automática</span>
            </div>

            <div id="historial-lista" class="screen-history-grid">
                @forelse ($llamados->skip(1)->take(7) as $turno)
                    <div class="screen-history-item">
                        <div>
                            <span class="screen-history-code">{{ $turno->codigo }}</span>
                            <span class="screen-history-service">{{ $turno->servicio?->nombre }}</span>
                        </div>
                        <div class="screen-history-window">
                            <span>{{ $turno->ventanilla?->codigo ?? '—' }}</span>
                            <small>{{ $turno->llamado_at?->format('H:i') }}</small>
                        </div>
                    </div>
                @empty
                    <div id="historial-vacio" class="screen-history-empty">
                        <i class="bi bi-clock-history"></i>
                        <span>Sin llamados anteriores.</span>
                    </div>
                @endforelse
            </div>
        </section>
    </main>

    <footer class="screen-footer">
        <span><span class="live-dot"></span> Sistema de turnos en tiempo real</span>
        <span>Los números permanecen visibles durante toda la jornada</span>
    </footer>
</div>

<script type="application/json" id="pantalla-inicial">@json($llamados->map(fn ($turno) => $turno->resumen())->values())</script>

<script>
    // Importar la función de escucha desde echo.js
    import { escucharTurnos, observarConexion } from '/js/echo.js';

    // Eventos que queremos escuchar para actualizar la pantalla
    const eventos = ['turno.llamado', 'turno.actualizado'];

    // Cuando llega un nuevo evento, recargar los datos de la pantalla
    escucharTurnos(eventos, (payload) => {
        // Mostrar notificación visual con acción
        const notificacion = new NotificationTurno(payload.turno, payload.accion);
        notificacion.mostrar();

        // Recargar los datos después de un corto retraso
        setTimeout(() => {
            window.location.reload();
        }, 1500);
    });

    // Observar estado de conexión
    observarConexion((conectado) => {
        const estado = document.getElementById('conexion-estado');
        if (conectado) {
            estado.innerHTML = '<span class="live-dot"></span> Conectado</span>';
        } else {
            estado.innerHTML = '<span class="live-dot"></span> Desconectado</span>';
        }
    });

    // Constructor de notificación visual
    function NotificationTurno(turno, accion) {
        this.turno = turno;
        this.accion = accion;
        this.elemento = null;

        this.mostrar = function() {
            // Determinar el mensaje basado en la acción
            let mensaje = '';
            switch (this.accion) {
                case 'repetir_llamado_audio':
                    mensaje = 'Acerca-se a la ventanilla – llamado repetido';
                    break;
                case 'llamar_audio':
                    mensaje = 'Llamando al siguiente turno';
                    break;
                case 'repetir_llamado':
                    mensaje = 'Llamado repetido – acércate a la ventanilla';
                    break;
                default:
                    mensaje = 'Turno llamado';
            }

            this.elemento = document.createElement('div');
            this.elemento.className = 'screen-notification';
            this.elemento.innerHTML = `
                <div class="screen-notification-content">
                    <span class="screen-notification-ticket">${this.turno.codigo}</span>
                    <span class="screen-notification-service">${this.turno.servicio?.nombre}</span>
                    <span class="screen-notification-accion">${mensaje}</span>
                    <button class="screen-notification-close" onclick="this.parentElement.parentElement.remove()">×</button>
                </div>
            `;
            document.body.appendChild(this.elemento);
            // Auto-ocultar según el tipo de acción (2-3 segundos)
            const duracion = this.accion.includes('repetir') ? 3000 : 2000;
            setTimeout(() => this.elemento.remove(), duracion);
        };
    }
</script>
</body>
</html>
