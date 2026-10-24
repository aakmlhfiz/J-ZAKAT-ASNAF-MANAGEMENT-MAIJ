import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

const token = document.querySelector('meta[name="csrf-token"]')?.content || '';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: (typeof import.meta !== 'undefined' && import.meta.env?.VITE_REVERB_APP_KEY) || 'jcrdgsmfl5o0qrm4npfi',
    wsHost: (typeof import.meta !== 'undefined' && import.meta.env?.VITE_REVERB_HOST) || 'localhost',
    wsPort: (typeof import.meta !== 'undefined' && import.meta.env?.VITE_REVERB_PORT) || 8080,
    wssPort: (typeof import.meta !== 'undefined' && import.meta.env?.VITE_REVERB_PORT) || 8080,
    forceTLS: (typeof import.meta !== 'undefined' && import.meta.env?.VITE_REVERB_SCHEME) === 'https',
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': token,
        },
    },
});
