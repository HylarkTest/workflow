import { startsWith } from 'lodash';
import { unref } from 'vue';
import {
    checkIfAuthenticated,
    loadAuthenticatedUser,
    user,
} from '@/core/auth.js';
import { getRememberedRoute } from '@/router/redirectToRememberedRoute.js';

export default async function guestMiddleware(to, from, options, router) {
    const isAuthenticated = await checkIfAuthenticated();
    if (isAuthenticated) {
        await loadAuthenticatedUser();
        if (!unref(user).finishedRegistration && !startsWith(to.name, 'register.')) {
            const route = await getRememberedRoute(
                router,
                ['savedRegistrationPage', 'server'],
                { name: 'register.start' }
            );
            return route;
        }
        if (to.query.redirect) {
            return { fullPath: to.query.redirect };
        }
        return { name: 'home' };
    }

    return to;
}
