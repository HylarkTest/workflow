import * as Sentry from '@sentry/vue';
import release from 'RELEASE';

import { excludeGraphQLFetch } from 'apollo-link-sentry';
import config from '@/core/config.js';
import { onAuthenticated, onLogout } from '@/core/auth.js';
import { isProduction } from '@/core/utils.js';

export default function initializeSentry(app, router) {
    Sentry.init({
        enabled: config('sentry.report'),
        app,
        dsn: config('sentry.dsn'),
        release,
        integrations: [
            new Sentry.BrowserTracing({
                routingInstrumentation: Sentry.vueRouterInstrumentation(router),
                tracingOrigins: [config('app.api-url'), /^\//],
            }),
        ],
        beforeBreadcrumb: excludeGraphQLFetch,
        trackComponents: true,
        // tracesSampleRate: parseFloat(config('sentry.traceSampleRate')),
        tracesSampleRate: 1,
        debug: false,
        environment: config('app.env'),
        autoSessionTracking: true,
        beforeSend(event, hint) {
            const ignoreStatuses = [422, 401, 500];
            const ignoreCategories = ['validation', 'internal', 'generic'];

            const error = hint?.originalException;
            const gqlError = error?.gqlError || _.first(error?.graphQLErrors);
            const networkError = error?.networkError || error;
            if (
                ignoreStatuses.includes(networkError?.response?.status)
                || ignoreCategories.includes(gqlError?.extensions?.category)
                // Handled in router.js
                || /^Failed to fetch dynamically imported module/i.test(error?.message || '')
                || /^'text\/html' is not a valid JavaScript MIME type/i.test(error?.message || '')
                || error?.message === 'Network Error'
            ) {
                return error.force ? event : null;
            }
            return event;
        },
        ignoreErrors: [
            // Random plugins/extensions
            'top.GLOBALS',
            // See: http://blog.errorception.com/2012/03/tale-of-unfindable-js-error.html
            'originalCreateNotification',
            'canvas.contentDocument',
            'MyApp_RemoveAllHighlights',
            'http://tt.epicplay.com',
            'Can\'t find variable: ZiteReader',
            'jigsaw is not defined',
            'ComboSearch is not defined',
            'http://loading.retry.widdit.com/',
            'atomicFindClose',
            // Facebook borked
            'fb_xd_fragment',
            // ISP "optimizing" proxy - `Cache-Control: no-transform` seems to reduce this. (thanks @acdha)
            // See http://stackoverflow.com/questions/4113268/how-to-stop-javascript-injection-from-vodafone-proxy
            'bmi_SafeAddOnload',
            'EBCallBackMessageReceived',
            // See http://toolbar.conduit.com/Developer/HtmlAndGadget/Methods/JSInjection.aspx
            'conduitPage',
            // Generic error code from errors outside the security sandbox
            // You can delete this if using raven.js > 1.0, which ignores these automatically.
            'Script error.',
        ],
        ignoreUrls: [
            // Facebook flakiness
            /graph\.facebook\.com/i,
            // Facebook blocked
            /connect\.facebook\.net\/en_US\/all\.js/i,
            // Woopra flakiness
            /eatdifferent\.com\.woopra-ns\.com/i,
            /static\.woopra\.com\/js\/woopra\.js/i,
            // Chrome extensions
            /extensions\//i,
            /^chrome:\/\//i,
            // Other plugins
            /127\.0\.0\.1:4001\/isrunning/i, // Cacaoweb
            /webappstoolbarba\.texthelp\.com\//i,
            /metrics\.itunes\.apple\.com\.edgesuite\.net\//i,
            /safari-extension:\/\//i,
        ],
        logErrors: !isProduction,
    });

    onAuthenticated((newUser) => {
        const userCanShowInSentry = !isProduction || /@hylark.com$/.test(newUser?.email || '');
        Sentry.configureScope((scope) => newUser.id && scope.setUser({
            id: atob(newUser.id),
            username: userCanShowInSentry ? newUser.name : '',
            email: userCanShowInSentry ? newUser.email : '',
        }));
    });

    onLogout(() => {
        Sentry.configureScope((scope) => scope.setUser(null));
    });
}
