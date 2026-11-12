import { escucharTurnos, observarConexion } from '../echo';

const pantalla = document.getElementById('pantalla-turnos');

if (pantalla) {
    const ahora = document.getElementById('pantalla-ahora');
    const historial = document.getElementById('historial-lista');
    const pendientes = document.getElementById('pendientes-valor');
    const conexion = document.getElementById('conexion-estado');
    const datosUrl = pantalla.dataset.datosUrl;

    let llamados = [];

    try {
        llamados = JSON.parse(document.getElementById('pantalla-inicial')?.textContent ?? '[]');
    } catch {
        llamados = [];
    }

    const escapar = (valor) =>
        String(valor ?? '').replace(/[&<>"']/g, (caracter) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;',
        }[caracter]));

    const hora = (fecha) => {
        if (!fecha) {
            return '';
        }

        return new Intl.DateTimeFormat('es', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false,
        }).format(new Date(fecha));
    };

    const renderizarActual = (turno, animar = false) => {
        if (!turno) {
            ahora.classList.add('is-empty');
            ahora.innerHTML = `
                <div class="screen-empty">
                    <i class="bi bi-megaphone"></i>
                    <h2>Esperando el primer llamado</h2>
                    <p>Los turnos llamados aparecerán aquí automáticamente.</p>
                </div>`;
            return;
        }

        ahora.classList.remove('is-empty');
        ahora.innerHTML = `
            <div class="screen-ticket-side">
                <span class="screen-label">Turno</span>
                <div class="screen-ticket-code">${escapar(turno.codigo)}</div>
                <div class="screen-service">${escapar(turno.servicio?.nombre)}</div>
                <div class="screen-area">${escapar(turno.area?.nombre)}</div>
            </div>
            <div class="screen-window-side">
                <span class="screen-label">Ventanilla</span>
                <div class="screen-window-code">${escapar(turno.ventanilla?.codigo ?? '—')}</div>
                <span class="screen-called-at">${escapar(hora(turno.llamado_at))}</span>
            </div>`;

        if (animar) {
            ahora.classList.add('screen-flash');
            window.setTimeout(() => ahora.classList.remove('screen-flash'), 900);
        }
    };

    const renderizarHistorial = () => {
        const anteriores = llamados.slice(1, 8);

        if (!anteriores.length) {
            historial.innerHTML = `
                <div id="historial-vacio" class="screen-history-empty">
                    <i class="bi bi-clock-history"></i>
                    <span>Sin llamados anteriores.</span>
                </div>`;
            return;
        }

        historial.innerHTML = anteriores.map((turno) => `
            <div class="screen-history-item">
                <div>
                    <span class="screen-history-code">${escapar(turno.codigo)}</span>
                    <span class="screen-history-service">${escapar(turno.servicio?.nombre)}</span>
                </div>
                <div class="screen-history-window">
                    <span>${escapar(turno.ventanilla?.codigo ?? '—')}</span>
                    <small>${escapar(hora(turno.llamado_at))}</small>
                </div>
            </div>`).join('');
    };

    const renderizar = (animar = false) => {
        renderizarActual(llamados[0] ?? null, animar);
        renderizarHistorial();
    };

    const sincronizar = async () => {
        if (!datosUrl) {
            return;
        }

        try {
            const respuesta = await fetch(datosUrl, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!respuesta.ok) {
                return;
            }

            const datos = await respuesta.json();
            llamados = datos.llamados ?? [];
            pendientes.textContent = datos.pendientes ?? pendientes.textContent;
            renderizarHistorial();
            renderizarActual(llamados[0] ?? null);
        } catch {
            // La pantalla conserva los datos del último evento recibido.
        }
    };

    renderizar();

    escucharTurnos(['turno.llamado'], (payload) => {
        const turno = payload?.turno;

        if (!turno?.ventanilla) {
            return;
        }

        llamados = [turno, ...llamados.filter((item) => item.id !== turno.id)].slice(0, 8);
        renderizarActual(turno, true);
        renderizarHistorial();
        sincronizar();
    });

    escucharTurnos(['turno.creado', 'turno.actualizado'], sincronizar);

    observarConexion((conectado) => {
        if (!conexion) {
            return;
        }

        conexion.classList.toggle('is-offline', !conectado);
        conexion.innerHTML = `<span class="live-dot"></span> ${conectado ? 'En vivo' : 'Reconectando…'}`;
    });

    const actualizarReloj = () => {
        const fecha = new Date();

        document.getElementById('reloj-hora').textContent = new Intl.DateTimeFormat('es', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false,
        }).format(fecha);

        document.getElementById('reloj-fecha').textContent = new Intl.DateTimeFormat('es', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
        }).format(fecha);
    };

    actualizarReloj();
    window.setInterval(actualizarReloj, 1000);
}
