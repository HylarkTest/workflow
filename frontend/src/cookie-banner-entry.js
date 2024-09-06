import './style/cookie-banner-main.css';

import _ from 'lodash';

import { createApp, h } from 'vue';

import i18n, { loadLocaleMessages, setI18nLanguage } from '@/i18n.js';

import dayjs from '@/core/plugins/initDayjs.js';

import FaIcon from '@/components/images/FaIcon.vue';

import {
    firstKey, getFirstKey, pascalCase, upperSnake,
} from '@/core/utils.js';

import { createAccentClasses, defaultAccentColor } from '@/core/display/accentColors.js';
import {
    COOKIE_SET,
    doesCookieExist,
    allowsFunctionalCookies,
    allowsAnalyticsCookies,
} from '@/core/helpers/cookieHelpers.js';

import CookieBanner from '@/components/access/CookieBanner.vue';

import eventBus from '@/core/eventBus.js';

window._ = _;
window.dayjs = dayjs;
window.hylarkEventBus = eventBus;
window.hylarkEvents = {
    COOKIE_SET,
};
window.onHylarkCookieSet = (callback) => {
    eventBus.listen(COOKIE_SET, callback);
};
window.allowsFunctionalCookies = allowsFunctionalCookies;
window.allowsAnalyticsCookies = allowsAnalyticsCookies;

_.mixin({
    pascalCase,
    upperSnake,
    firstKey,
    getFirstKey,
});

const app = createApp({
    render() {
        return this.showCookiesBanner
            ? h(CookieBanner, {
                onCloseCookiesBanner: () => {
                    this.showCookiesBanner = false;
                },
            })
            : null;
    },
    data() {
        return {
            showCookiesBanner: !doesCookieExist('hylark_cookies_permissions'),
        };
    },
});

export default app;

app.config.globalProperties.$dayjs = dayjs;

app.use(i18n);

app.component('FaIcon', FaIcon);

const css = createAccentClasses(defaultAccentColor, 'LIGHT');
const styleNode = document.createElement('style');
styleNode.innerHTML = css;
document.head.appendChild(styleNode);

function enableTrackingIfConsentGiven() {
    if (allowsAnalyticsCookies() && window._paq) {
        window._paq.push(['setConsentGiven']);
        window._paq.push(['setCookieConsentGiven']);
    }
}

(async () => {
    await loadLocaleMessages('en');
    setI18nLanguage('en');
    app.mount('#hylark-cookie-banner');

    enableTrackingIfConsentGiven();

    eventBus.listen(COOKIE_SET, enableTrackingIfConsentGiven);
})();
