import './style/templates-main.css';

import _ from 'lodash';

import { createApp, h } from 'vue';

import i18n, { loadLocaleMessages, setI18nLanguage } from '@/i18n.js';

import markdownTextDirective from '@/core/plugins/markdownTextDirective.js';

import {
    firstKey, getFirstKey, pascalCase, upperSnake,
} from '@/core/utils.js';

import { createAccentClasses, defaultAccentColor } from '@/core/display/accentColors.js';
import TemplatesApp from '@/TemplatesApp.vue';

window._ = _;

_.mixin({
    pascalCase,
    upperSnake,
    firstKey,
    getFirstKey,
});

const app = createApp({
    render() {
        return h(TemplatesApp);
    },
});

export default app;

app.use(markdownTextDirective);
app.use(i18n);

const css = createAccentClasses(defaultAccentColor, 'LIGHT');
const styleNode = document.createElement('style');
styleNode.innerHTML = css;
document.head.appendChild(styleNode);

(async () => {
    await loadLocaleMessages('en');
    setI18nLanguage('en');
    app.mount('#hylark-templates');
})();
