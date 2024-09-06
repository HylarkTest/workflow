/* eslint-disable no-console */
import Pusher from 'pusher-js';
import authorizationHandler from './authorizationHandler.js';

import config from '@/core/config.js';

const pusher = new Pusher(config('pusher.app-key'), {
    enabledTransports: ['ws', 'wss'],
    cluster: config('pusher.cluster'),
    channelAuthorization: {
        customHandler: authorizationHandler,
    },
    wsHost: config('pusher.host'),
    wsPort: config('pusher.port'),
    forceTLS: config('pusher.force-tls'),
    disableStats: config('pusher.disable-stats'),
});

pusher.formatEventName = (dotName) => {
    return `App\\Events\\${dotName.replace(/\./g, '\\')}`;
};

if (config('app.env') !== 'production') {
    pusher.connection.bind('error', (error) => console.log('Pusher ERROR', error));
}

export default pusher;
