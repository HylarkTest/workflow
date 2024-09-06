import axios from 'axios';

import { isGuest } from '@/core/auth.js';

const sanitizeKey = encodeURI;

function send(method, key, data) {
    if (isGuest()) {
        return Promise.resolve(null);
    }

    const url = `/store/${sanitizeKey(key)}`;

    return axios({
        method,
        url,
        data,
    });
}

export function store(key, data) {
    return send('post', key, { value: JSON.stringify(data) });
}

export function get(key) {
    return send(
        'get',
        key
    ).then((response) => {
        return response?.data?.data ? JSON.parse(response.data.data) : null;
    });
}

export function clear(key) {
    return send('delete', key);
}
