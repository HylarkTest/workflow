import axios from 'axios';
import _ from 'lodash';
import {
    computed, readonly, ref, unref,
} from 'vue';

import pusher from '@/core/pusher/main.js';

import {
    defaultApolloClient,
    onLogout as apolloLogoutHandler,
    onLogin as apolloLoginHandler,
} from '@/http/apollo/defaultApolloClient.js';

import watchUser from '@/core/repositories/userRepository.js';
import config from '@/core/config.js';

import ME from '@/graphql/Me.gql';
import { isHttpError } from '@/http/checkResponse.js';
import { bases, loadBases, reloadBases } from '@/core/repositories/baseRepository.js';
import { initializeCSRF } from '@/http/apollo/graphqlClient.js';

let sessionStart = null;

const userRef = ref();

export const user = readonly(userRef);

// Login listeners trigger when the user actively logs in.
// Authenticated listeners trigger when the user is authenticated which includes
// logging in or refreshing the page. These listeners include the user object.
const loginListeners = [];
const authenticatedListeners = [];
const logOutListeners = [];

export function resetSession() {
    sessionStart = new Date().getTime();
}

function shouldCheckSession() {
    if (!sessionStart) {
        return true;
    }
    const now = new Date().getTime();

    return (now - sessionStart) > (config('session.lifetime') * 60 * 500);
}

export function loadAuthenticatedUser(force = false) {
    return new Promise((resolve, reject) => {
        if (userRef.value && !shouldCheckSession()) {
            resolve();
        } else {
            watchUser((sub) => {
                const isNew = sub.data.user.id !== userRef.value?.id;
                const newUser = sub.data.user;
                userRef.value = newUser;
                resetSession();
                if (isNew) {
                    authenticatedListeners.forEach((listener) => listener(userRef.value));
                }
                resolve();
            }, (error) => {
                userRef.value = null;
                reject(error);
            }, force, { errorPolicy: 'all' });
        }
    });
}

export function loadUserAndBases() {
    return Promise.all([
        loadAuthenticatedUser(),
        loadBases(),
    ]);
}

export async function login(form) {
    await initializeCSRF(true);
    const response = await form.post('/login');
    if (response.data?.two_factor || response.data?.one_time_password) {
        return Promise.reject(response.data);
    }
    if (response.data?.redirect) {
        window.location.href = response.data.redirect;
        // Pause for a moment to allow the redirect to happen
        await new Promise((resolve) => {
            window.setTimeout(resolve, 5000);
        });
    } else {
        loginListeners.forEach((listener) => listener());
    }

    await loadUserAndBases();
    return Promise.resolve();
}

export async function twoFa(form, otp = false) {
    await form.post(otp ? '/one-time-password' : '/two-factor-challenge');
    await loadUserAndBases();
}

export async function register(form) {
    await initializeCSRF(true);
    await form.post('/register');
    await loadUserAndBases();
}

export function checkRegistration(form) {
    return form.post('/register-check');
}

export async function bootstrapUser(blueprint) {
    const response = await axios.post('/bootstrap', blueprint);

    if (isHttpError(response)) {
        window.location.reload();
    } else {
        await Promise.all([
            defaultApolloClient().writeQuery({
                query: ME,
                data: {
                    user: {
                        ...userRef.value,
                        finishedRegistration: true,
                    },
                },
            }),
            reloadBases(),
        ]);
    }
}

export function setLogout() {
    logOutListeners.forEach((listener) => listener());
    userRef.value = null;
}
window.setLogout = setLogout;

export async function logout() {
    await axios.post('/logout');
    window.location.href = '/login';
}

export async function updateEmail(form) {
    const response = await form.post('/user/email');
    return response;
}

export async function verifyUpdateEmail(form) {
    const response = await form.post('/user/email/verify');
    return response;
}

export async function checkPassword(form) {
    const response = await form.post('/user/password/check');
    return response;
}

export async function updatePassword(form) {
    const response = await form.put('/user/password');
    return response;
}

export async function get2faSvg() {
    await axios.post('/user/two-factor-authentication');
    return axios.get('/user/two-factor-qr-code');
}

export async function confirm2fa(form) {
    await form.post('/user/confirmed-two-factor-authentication');

    userRef.value.hasEnabledTwoFactorAuthentication = true;
}

export async function disable2fa(form) {
    await form.delete('/user/two-factor-authentication');

    userRef.value.hasEnabledTwoFactorAuthentication = false;
}

export function forgotPassword(form) {
    return form.post('/forgot-password');
}

export function resetPassword(form) {
    return form.post('/reset-password');
}

const authenticatedUser = computed(() => {
    const userRaw = unref(userRef);
    // Only return the authenticated user once the bases have been loaded
    if (!userRaw || !unref(bases).length) {
        return null;
    }
    return userRaw;
});

export function getAuthenticatedUser() {
    return authenticatedUser;
}

export function isVerified() {
    return !!unref(userRef)?.verified;
}

export function isGuest() {
    return !unref(userRef);
}

export async function checkIfAuthenticated() {
    const response = await axios.get('/auth/check');
    return response.data.authenticated;
}

export const isAuthenticated = _.negate(isGuest);

export function onLogin(listener) {
    loginListeners.push(listener);
}

export function onAuthenticated(listener) {
    authenticatedListeners.push(listener);
}

export function onLogout(listener) {
    logOutListeners.push(listener);
}

function subscribeUser(userModel) {
    const loginChannel = pusher.subscribe(`private-login-channel.${userModel.id}`);
    loginChannel.bind(pusher.formatEventName('Auth.LoggedOut'), () => {
        window.location.href = '/login';
    });
}

onLogin(() => apolloLoginHandler(defaultApolloClient()));
onAuthenticated((userModel) => subscribeUser(userModel));
onLogout(() => apolloLogoutHandler(defaultApolloClient()));
