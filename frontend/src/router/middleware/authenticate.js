import { startsWith } from 'lodash';
import {
    checkIfAuthenticated,
    getAuthenticatedUser,
    loadUserAndBases,
} from '@/core/auth.js';
import { getRememberedRoute } from '@/router/redirectToRememberedRoute.js';
import getUserPreferences from '@/core/repositories/preferencesRepository.js';

export default async function authenticateMiddleware(to, from, options, router) {
    const isAuthenticated = await checkIfAuthenticated();
    if (isAuthenticated) {
        await Promise.all([
            loadUserAndBases(),
            getUserPreferences(),
        ]);
    }
    if (!isAuthenticated) {
        return {
            name: 'access.login',
            query: { redirect: encodeURI(to.fullPath) },
        };
    }
    const user = getAuthenticatedUser();
    if (!user.value.finishedRegistration && !startsWith(to.name, 'register.')) {
        return getRememberedRoute(router, ['savedRegistrationPage', 'server'], { name: 'register.uses' });
    }
    if (user.value.finishedRegistration && startsWith(to.name, 'register.')) {
        return { name: 'home' };
    }
    return to;
}
