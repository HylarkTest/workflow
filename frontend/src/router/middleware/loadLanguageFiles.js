import config from '@/core/config.js';
import { loadLocaleMessages, setI18nLanguage, SUPPORT_LOCALES } from '@/i18n.js';

export default async function loadLanguageFilesMiddleware(to) {
    const paramsLocale = to.params.locale || config('locale.lang');

    if (SUPPORT_LOCALES.includes(paramsLocale)) {
        await loadLocaleMessages(paramsLocale);
        setI18nLanguage(paramsLocale);
    }
}
