import './style/main.css';

import axios from 'axios';
import { loadErrorMessages, loadDevMessages } from '@apollo/client/dev';

import { Sortable, OnSpill } from 'sortablejs/modular/sortable.core.esm';
import './core/initLodash.js';

import app from './app.js';

import router from './router.js';
import i18n from './i18n.js';

import blur from './core/plugins/blurDirective.js';
import markdownTextDirective from './core/plugins/markdownTextDirective.js';
import dayjs from './core/plugins/initDayjs.js';
import formlaPlugin from './core/plugins/formlaPlugin.js';
import proxyEventPlugin from './core/plugins/proxyEventPlugin.js';
import VueDOMPurifyHTML from './core/plugins/vueDompurifyHtml.js';

import importCommonComponents from './commonComponents.js';
import initForm from './core/initForm.js';
import userFeedbackGenerators from './core/uiGenerators/userFeedbackGenerators.js';
import support from './core/support.js';

import FaIcon from './components/images/FaIcon.vue';
import { clearExpiredStorage } from '@/core/localStorage.js';
import config from '@/core/config.js';
import exceptionHandler, { handleVueError } from '@/http/exceptionHandler.js';
import { initializeCSRF } from '@/http/apollo/graphqlClient.js';
import initializeSentry from '@/sentry.js';
import { onLogout } from '@/core/auth.js';
import '@/core/handleSessionTimeout.js';

import {
    dateDayjsFormat,
    dateInFormat,
    dateInUtc,
    dateWithTz,
    formattedTime,
    timeDayjsFormat,
} from '@/core/repositories/preferencesRepository.js';

import { instantiate } from '@/core/utils.js';
import { isHttpError } from '@/http/checkResponse.js';
import List from '@/core/models/List.js';
import ListItem from '@/core/models/ListItem.js';
import initializeMatomo from '@/matomo/initMatomo.js';

// eslint-disable-next-line no-unused-expressions
import(
    'focus-visible/dist/focus-visible.min.js'
);

// Need to figure out why the screen is blank after deploy
// if ('serviceWorker' in navigator) {
//     // If active service worker found, no need to register
//     if (!navigator.serviceWorker.controller) {
//         navigator.serviceWorker.register('sw.js', { scope: './' });
//     }
// }

// Using this instead of `isProduction` to hopefully avoid loading these in the
// production build
if (import.meta.env.MODE !== 'production') {
    loadDevMessages();
    loadErrorMessages();
}

clearExpiredStorage();

Sortable.mount(OnSpill);

axios.interceptors.request.use(async (conf) => {
    if (conf.url !== '/csrf-cookie') {
        try {
            await initializeCSRF();
        } catch (error) {
            initializeCSRF.flush();
            if (!isHttpError(error)) {
                throw error;
            }
        }
    }
    return conf;
});

onLogout(() => {
    initializeCSRF.flush();
    initializeCSRF();
});

window.axios = axios;
window.dayjs = dayjs;

window.utils = {
    dateInUtc,
    dateWithTz,
    formattedTime,
    timeDayjsFormat,
    dateDayjsFormat,
    dateInFormat,
};

axios.defaults.withCredentials = true;

initForm();

app.config.globalProperties.$dayjs = dayjs;
// Helper methods for generating dummy data
app.config.globalProperties.$dummyList = (data) => instantiate(data, List);
app.config.globalProperties.$dummyItem = (data) => instantiate(data, ListItem);
app.config.errorHandler = handleVueError;

function errorHandler(error) {
    const handled = exceptionHandler(error);
    if (config('app.env') === 'production') {
        return true;
    }
    if (config('debug.alertOnHttpError')) {
        return false;
    }
    return handled;
}

window.onerror = (message, location, lineno, colno, error) => {
    return errorHandler(error);
};

window.onunhandledrejection = (error) => {
    return !errorHandler(error);
};

initializeSentry(app, router);

app.use(blur);
app.use(markdownTextDirective);
app.use(formlaPlugin);
app.use(i18n);
app.use(proxyEventPlugin);
app.use(router);
app.use(userFeedbackGenerators);
app.use(support);
app.use(VueDOMPurifyHTML);

initializeMatomo(app, router);

importCommonComponents(app);
app.component('FaIcon', FaIcon);

app.mount('#app');
