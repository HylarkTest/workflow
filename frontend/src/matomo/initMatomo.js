import VueMatomo from './vueMatomo.js';
import config from '@/core/config.js';
import { onAuthenticated, onLogout } from '@/core/auth.js';
import { allowsAnalyticsCookies, COOKIE_SET } from '@/core/helpers/cookieHelpers.js';
import eventBus from '@/core/eventBus.js';

function enableTrackingIfConsentGiven() {
    if (allowsAnalyticsCookies()) {
        window._paq.push(['setConsentGiven']);
        window._paq.push(['setCookieConsentGiven']);
    }
}

export default function initializeMatomo(app, router) {
    if (config('app.env') === 'production') {
        app.use(VueMatomo, {
            host: config('matomo.host'),
            siteId: config('matomo.siteId'),
            router,
            enableLinkTracking: true,
            requireConsent: true,
            requireCookieConsent: true,
            enableHeartBeatTimer: true,
            domains: `*.${config('app.api-url')}`,
        });

        onAuthenticated((newUser) => {
            if (window._paq) {
                window._paq.push(['setUserId', atob(newUser.id)]);
            }
        });

        onLogout(() => {
            if (window._paq) {
                window._paq.push(['resetUserId']);
                window._paq.push(['appendToTrackingUrl', 'new_visit=1']);
                window._paq.push(['trackPageView']);
                window._paq.push(['appendToTrackingUrl', '']);
            }
        });

        enableTrackingIfConsentGiven();

        eventBus.listen(COOKIE_SET, enableTrackingIfConsentGiven);
    }
}
