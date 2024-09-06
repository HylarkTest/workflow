import { createI18n } from 'vue-i18n';
import { nextTick } from 'vue';
import _ from 'lodash';
import getMessages from 'getMessages'; // Handled with resolve plugin
import { isProduction } from '@/core/utils.js';
import config from '@/core/config.js';

export const SUPPORT_LOCALES = ['de', 'en', 'es', 'fr'];

export const i18nPlugin = createI18n({
    locale: config('locale.lang'),
    fallbackLocale: config('locale.fallback'),
});

export async function loadLocaleMessages(locale) {
    if (i18nPlugin.global.availableLocales.includes(locale) && !_.isEmpty(i18nPlugin.global.messages[locale])) {
        return;
    }
    const messages = await getMessages(locale);

    i18nPlugin.global.setLocaleMessage(locale, messages);

    await nextTick();
}

export function setI18nLanguage(locale) {
    if (i18nPlugin.mode === 'legacy') {
        i18nPlugin.global.locale = locale;
    } else {
        i18nPlugin.global.locale.value = locale;
    }
    document.querySelector('html').setAttribute('lang', locale);
}

export const $t = i18nPlugin.global.t;
export const _t = i18nPlugin.global.t; // For composition API

/**
 * Fetch the raw translated message without replacing any wildcards or fancy
 * formatting. This could be used to return an object of messages for looping.
 * @param key
 * @returns string|object
 */

export function $translationMessage(key) {
    return _.get(i18nPlugin.global.getLocaleMessage(i18nPlugin.global.locale), key);
}

export function $translationExists(key) {
    return !_.isUndefined($translationMessage(key));
}

export function $translationRaw(key, fallback) {
    if ($translationExists(key)) {
        return $translationMessage(key);
    }
    if (!isProduction && _.isUndefined(fallback)) {
        // eslint-disable-next-line no-console
        console.warn(`[hylark] Not found '${key}' key in '${i18nPlugin.global.locale}' locale messages.`);
    }
    return fallback || key;
}

// export function $tr(key) {
//     const locale = i18nPlugin.global.locale;
//     const message = _.get(i18nPlugin.global.getLocaleMessage(locale), key);
//     if (_.isUndefined(message)) {
//         if (!isProduction) {
//             // eslint-disable-next-line no-console
//             console.warn(`[hylark] Not found '${key}' key in '${locale}' locale messages.`);
//         }
//         return key;
//     }
//     return message;
// }

const baseInstall = i18nPlugin.install;
i18nPlugin.install = (app) => {
    baseInstall(app);
    // eslint-disable-next-line no-param-reassign
    app.config.globalProperties.$tr = $translationRaw;
};

export default i18nPlugin;
