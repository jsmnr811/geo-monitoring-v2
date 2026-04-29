/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const csrfToken = document.querySelector('meta[name="csrf-token"]');
if (csrfToken) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
}

import './echo';

document.addEventListener('DOMContentLoaded', () => {
    console.log('App.js DOMContentLoaded');

    const userIdMeta = document.querySelector('meta[name="user-id"]');

    if (!userIdMeta) {
        console.log('No user-id meta found');
        return;
    }

    const userId = userIdMeta.getAttribute('content');
    console.log('User ID from meta:', userId);

    console.log('Setting up Echo listeners for sync.' + userId);

    const channel = window.Echo.private(`sync.${userId}`);
    channel.listen('.sync.success', (e) => {
        console.log('Received sync.success', e);
        const type = e.message === "Syncing in background" ? 'info' : 'success';
        window.dispatchEvent(new CustomEvent('toast', {
            detail: { message: e.message, type: type }
        }));
    })
    .listen('.sync.failed', (e) => {
        console.log('Received sync.failed', e);
        window.dispatchEvent(new CustomEvent('toast', {
            detail: { message: e.message, type: 'error' }
        }));
    });

    channel.subscribed(() => console.log('Subscribed to sync channel'));
    channel.error((error) => console.log('Sync channel subscription error', error));

});
