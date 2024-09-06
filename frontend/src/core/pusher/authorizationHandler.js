import axios from 'axios';
import config from '@/core/config.js';
import { activeBase } from '@/core/repositories/baseRepository.js';

export default async (params, callback) => {
    const channelName = encodeURIComponent(params.channelName);
    const socketId = encodeURIComponent(params.socketId);
    const query = `socket_id=${socketId}&channel_name=${channelName}`;

    const endPoint = channelName.includes('lighthouse')
        ? config('pusher.auth.subscriptions')
        : config('pusher.auth.broadcasts');

    try {
        const response = await axios.post(endPoint, query, {
            headers: {
                'X-Base-Id': activeBase()?.id,
            },
        });
        callback(null, response.data);
    } catch (e) {
        callback(e, null);
    }
};
