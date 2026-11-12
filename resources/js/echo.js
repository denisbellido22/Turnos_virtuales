import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const appKey = import.meta.env.VITE_REVERB_APP_KEY;
const scheme = import.meta.env.VITE_REVERB_SCHEME ?? 'http';
const port = Number(import.meta.env.VITE_REVERB_PORT ?? 80);

if (appKey) {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: appKey,
        wsHost: import.meta.env.VITE_REVERB_HOST ?? window.location.hostname,
        wsPort: scheme === 'https' ? Number(import.meta.env.VITE_REVERB_PORT ?? 443) : port,
        wssPort: Number(import.meta.env.VITE_REVERB_PORT ?? 443),
        forceTLS: scheme === 'https',
        enabledTransports: ['ws', 'wss'],
    });
}

/**
 * Escucha los eventos del canal público de turnos.
 *
 * @param {string[]} eventos
 * @param {(payload: { turno: object }) => void} callback
 */
export function escucharTurnos(eventos, callback) {
    if (!window.Echo) {
        return;
    }

    const canal = window.Echo.channel('turnos');

    eventos.forEach((evento) => {
        canal.listen(`.${evento}`, callback);
    });
}

/**
 * Informa el estado de la conexión WebSocket.
 *
 * @param {(conectado: boolean) => void} callback
 */
export function observarConexion(callback) {
    const pusher = window.Echo?.connector?.pusher;

    if (!pusher?.connection) {
        callback(false);
        return;
    }

    pusher.connection.bind('connected', () => callback(true));
    pusher.connection.bind('disconnected', () => callback(false));
    pusher.connection.bind('unavailable', () => callback(false));
    pusher.connection.bind('failed', () => callback(false));

    callback(pusher.connection.state === 'connected');
}
