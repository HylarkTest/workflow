import * as local from '@/core/localStorage.js';
import * as server from '@/core/serverStorage.js';

export default function rememberRouteMiddleware(to, from, options) {
    const cacheKey = options[0] || 'lastRoute';
    const store = options[1] === 'server' ? server : local;

    store.store(cacheKey, to.fullPath);
}
