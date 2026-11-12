import { escucharTurnos } from '../echo';

const detalle = document.getElementById('turno-detalle');

if (detalle) {
    const badge = document.getElementById('estado-badge');
    const posicion = document.getElementById('posicion-valor');
    const posicionTexto = document.getElementById('posicion-texto');
    const mensaje = document.getElementById('turno-mensaje');
    const ventanilla = document.getElementById('ventanilla-valor');
    const actualizado = document.getElementById('actualizado-en');
    const tarjeta = detalle.querySelector('.ticket-display');

    let temporizador = null;

    const escapar = (valor) =>
        String(valor ?? '').replace(/[&<>"']/g, (caracter) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;',
        }[caracter]));

    const actualizar = async () => {
        try {
            const respuesta = await fetch(detalle.dataset.estadoUrl, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!respuesta.ok) {
                return;
            }

            const datos = await respuesta.json();

            badge.innerHTML = `<span class="badge rounded-pill estado-badge ${escapar(datos.estado_clase)}">${escapar(datos.estado_etiqueta)}</span>`;

            if (datos.posicion) {
                posicion.textContent = datos.posicion;

                posicionTexto.textContent = datos.posicion === 1
                    ? 'Eres el siguiente en la fila del área.'
                    : `Hay ${datos.posicion - 1} ${datos.posicion - 1 === 1 ? 'persona' : 'personas'} antes que tú.`;
            } else {
                posicion.textContent = '—';
                posicionTexto.textContent = 'El turno ya no se encuentra en espera.';
            }

            const mensajes = {
                llamando: `¡Es tu turno! Acércate a la ventanilla ${datos.ventanilla ?? 'asignada'}.`,
                atendido: 'Tu atención fue completada. ¡Gracias por utilizar el sistema!',
                ausente: 'El turno fue marcado como ausente. Solicita uno nuevo si lo necesitas.',
                cancelado: 'Este turno fue cancelado.',
                pendiente: datos.posicion === 1
                    ? 'Eres el próximo en ser llamado.'
                    : 'Espera tu llamado; tu pantalla se actualizará automáticamente.',
            };

            mensaje.textContent = mensajes[datos.estado] ?? 'Espera tu llamado.';

            if (datos.ventanilla) {
                ventanilla.textContent = datos.ventanilla;
            }

            tarjeta.classList.toggle('ticket-display-calling', datos.estado === 'llamando');

            actualizado.textContent = new Intl.DateTimeFormat('es', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false,
            }).format(new Date());
        } catch {
            // Se mantiene la información ya visible.
        }
    };

    const programar = () => {
        window.clearTimeout(temporizador);
        temporizador = window.setTimeout(actualizar, 250);
    };

    escucharTurnos(['turno.creado', 'turno.llamado', 'turno.actualizado'], programar);
}
