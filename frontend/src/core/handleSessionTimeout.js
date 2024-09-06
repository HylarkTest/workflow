import _ from 'lodash';
import axios from 'axios';
import config from '@/core/config.js';
import { checkIfAuthenticated, isAuthenticated, onAuthenticated } from '@/core/auth.js';

const sessionTime = (config('session.lifetime') * 1000 * 60) + 5000; // 2 hours + 5 seconds

let timeoutId;

function logout() {
    const path = '/login?session_expired=true';
    window.location.replace(path);
}

const checkAuthenticated = _.throttle(() => {
    if (isAuthenticated()) {
        checkIfAuthenticated().then((result) => {
            if (!result && isAuthenticated()) {
                logout();
            }
        });
    }
    // eslint-disable-next-line no-use-before-define
    resetTimer();
}, sessionTime);

function resetTimer() {
    window.clearTimeout(timeoutId);
    timeoutId = window.setTimeout(checkAuthenticated, sessionTime);
}

resetTimer();

onAuthenticated(resetTimer);

axios.interceptors.request.use(async (conf) => {
    resetTimer();
    return conf;
});

['touchstart', 'mousedown', 'keydown', 'focus'].forEach((event) => {
    window.addEventListener(event, checkAuthenticated, false);
});
