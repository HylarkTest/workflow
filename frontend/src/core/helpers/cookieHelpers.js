import _ from 'lodash';
import config from '@/core/config.js';
import eventBus from '@/core/eventBus.js';

export const COOKIE_SET = Symbol('Cookie set');

export function setCookie(name, value, expiry = 365) {
    // Value in any format, object, boolean, etc... stringified below.
    // Expiry value in days
    const date = new Date();
    date.setTime(date.getTime() + (expiry * 24 * 60 * 60 * 1000));
    const expiresOn = date.toUTCString();
    const formattedValue = JSON.stringify(value);
    const encodedValue = encodeURIComponent(formattedValue);
    document.cookie = [
        `${name}=${encodedValue}`,
        `expires=${expiresOn}`,
        'path=/',
        `domain=.${config('app.landing-domain')}`,
        'samesite=strict',
    ].join(';');

    eventBus.dispatch(COOKIE_SET, [name, value, expiry]);
}

export function doesCookieExist(cookieName) {
    const cookieMatch = document.cookie.match(RegExp(`(?:^|;\\s*)${cookieName}=([^;]*)`));
    return cookieMatch ? cookieMatch[1] : null;
}

export function getCookieValue(cookieName) {
    const cookieValue = doesCookieExist(cookieName);
    if (cookieValue) {
        const decoded = decodeURIComponent(cookieValue);
        return JSON.parse(decoded);
    }
    return null;
}

export function checkSpecificCookieValue(cookieName, specificVal) {
    const decodedValue = getCookieValue(cookieName);
    if (!decodedValue) {
        return false;
    }
    if (_.isArray(specificVal)) {
        return _.every(specificVal, (val) => {
            return decodedValue.includes(val);
        });
    }
    if (_.isObject(specificVal)) {
        const objKeys = Object.keys(specificVal);
        return _.every(objKeys, (key) => {
            return specificVal[key] === decodedValue[key];
        });
    }
    if (_.isString(specificVal)) {
        return specificVal === decodedValue;
    }
    return false;
}

export function hasPermissionsCookie() {
    return doesCookieExist('hylark_cookies_permissions');
}

export function allowsFunctionalCookies() {
    return checkSpecificCookieValue('hylark_cookies_permissions', { functional: true });
}

export function allowsAnalyticsCookies() {
    return checkSpecificCookieValue('hylark_cookies_permissions', { analytics: true });
}
