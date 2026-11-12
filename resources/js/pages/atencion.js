import { escucharTurnos } from '../echo';

const panel = document.getElementById('atencion-panel');

if (panel) {
    const contenedor = document.getElementById('cola-contenido');
    let temporizador = null;

    const refrescar = async () => {
        try {
            const respuesta = await fetch(panel.dataset.colaUrl, {
                headers: {
                    Accept: 'text/html',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!respuesta.ok) {
                return;
            }

            contenedor.innerHTML = await respuesta.text();
        } catch {
            // Si falla la red, se conserva la última cola visible.
        }
    };

    const programarRefresco = () => {
        window.clearTimeout(temporizador);
        temporizador = window.setTimeout(refrescar, 300);
    };

    escucharTurnos(['turno.creado', 'turno.llamado', 'turno.actualizado'], programarRefresco);
}
