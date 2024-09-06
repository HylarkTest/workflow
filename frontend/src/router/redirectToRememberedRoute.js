import * as server from '@/core/serverStorage.js';
import * as local from '@/core/localStorage.js';

export async function getRememberedRoute(router, options, defaultRoute) {
    const cacheKey = options[0] || 'lastRoute';
    const store = options[1] === 'server' ? server : local;

    const route = await store.get(cacheKey);

    if (_.isPlainObject(route) && route.name && router.hasRoute(route.name)) {
        return route;
    }

    if (_.isString(route)) {
        const resolvedRoute = router.resolve(route);
        if (resolvedRoute.matched.length > 0 && resolvedRoute.matched[0].name !== 'not-found') {
            return resolvedRoute;
        }
    }

    return defaultRoute;
}

export default async function redirectToRememberedRoute(router, options, defaultRoute) {
    const rememberedRoute = await getRememberedRoute(router, options, defaultRoute);

    await router.push(rememberedRoute || { name: 'home' });
}
