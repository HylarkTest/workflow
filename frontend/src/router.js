import { createRouter, createWebHistory } from 'vue-router';

import { isFunction, isString } from 'lodash';
// import Home from '@/components/landing/Home';

import settingsRoutes from '@/routes/settings.js';
import userRoutes from '@/routes/user.js';
import featuresRoutes from '@/routes/features.js';
import accessRoutes from '@/routes/access.js'; // requires language keys
import mainRoutes from '@/routes/main.js';
import supportRoutes from '@/routes/support.js';

import auth from '@/router/middleware/authenticate.js';
import guest from '@/router/middleware/guest.js';
import role from '@/router/middleware/role.js';
import updateRouterTitle from '@/router/middleware/updateRouterTitle.js';
import loadLanguageFiles from '@/router/middleware/loadLanguageFiles.js';
import redirectToProxy from '@/router/middleware/redirectToProxy.js';
import remember from '@/router/middleware/rememberRoute.js';
import redirectBaseRoute from '@/router/middleware/redirectBaseRoute.js';
import switchToScopedBase from '@/router/middleware/switchToScopedBase.js';
import ErrorPage from '@/components/errors/ErrorPage.vue';
import { newReleaseAvailable } from '@/http/apollo/graphqlClient.js';
import config from '@/core/config.js';

const middlewareMap = {
    auth,
    remember,
    guest,
    role,
};

const defaultMiddleware = [
    loadLanguageFiles,
    redirectToProxy,
    updateRouterTitle,
];

const landingPages = [
    'features', 'pricing', 'templates', 'about-us',
];

function addBaseMiddleware(routes) {
    return routes.map((route) => ({
        ...route,
        ...(route.meta?.baseScoped ? {
            path: `/:baseId?${route.path}`,
        } : {}),
        meta: {
            ...route.meta,
            mw: (route.meta?.mw || []).concat(route.meta?.baseScoped ? [
                switchToScopedBase,
                redirectBaseRoute,
            ] : []),
        },
    }));
}

const routes = addBaseMiddleware([
    ...settingsRoutes,
    ...userRoutes,
    ...featuresRoutes,
    ...accessRoutes,
    ...mainRoutes,
    ...supportRoutes,
    {
        path: '/error',
        name: 'error',
        component: ErrorPage,
        meta: { noNav: true, error: true },
    },
    ...landingPages.map((path) => ({
        path: `/${path}`,
        component: ErrorPage,
        props: () => ({ status: '404' }),
        beforeEnter: () => {
            const landingUrl = config('app.landing-url');
            if (!window.location.href.includes(landingUrl)) {
                window.location.href = `${config('app.landing-url')}/${path}`;
            }
        },
    })),
    {
        path: '/:pathMatch(.*)*',
        alias: '/404',
        name: 'not-found',
        component: ErrorPage,
        props: () => ({ status: '404' }),
        meta: { noNav: true, error: true },
    },
]);

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export function redirectGuest() {
    const to = router.currentRoute.value;
    if (to.meta.mw && to.meta.mw.includes('auth')) {
        window.location.href = '/login';
    }
}
// Causes too many dependency cycles, it's very annoying
window.redirectGuest = redirectGuest;

// let previousRoute;

router.beforeEach(async (to, from) => {
    const shouldReload = await newReleaseAvailable();

    if (shouldReload) {
        window.location.href = to.fullPath;
        return false;
    }
    const matchedMiddleware = _.uniq(to.matched.flatMap((route) => route.meta.mw || []));
    const routeMiddleware = defaultMiddleware.concat(matchedMiddleware);

    for (let i = 0; i < routeMiddleware.length; i += 1) {
        const middleware = routeMiddleware[i];
        let result;
        if (isString(middleware)) {
            const split = middleware.split(':');
            const handler = middlewareMap[split[0]];
            const options = split[1]?.split(',') || [];

            // eslint-disable-next-line no-await-in-loop
            result = await handler(to, from, options, router);
        } else if (isFunction(middleware)) {
            // eslint-disable-next-line no-await-in-loop
            result = await middleware(to, from, undefined, router);
        } else {
            // eslint-disable-next-line no-await-in-loop
            result = await middleware.handler(to, from, middleware.options, router);
        }

        if (result === false) {
            return false;
        }

        if (_.isPlainObject(result) && result !== to) {
            return result;
        }
    }

    return true;
});

export default router;

// export function getPreviousRoute() {
//     return previousRoute;
// }

router.onError((error, to) => {
    if (
        /(Failed to fetch dynamically imported module|'text\/html' is not a valid)/i.test(error.message)
        && navigator.onLine
    ) {
        window.location.href = to.fullPath;
    }
});

window.router = router;
