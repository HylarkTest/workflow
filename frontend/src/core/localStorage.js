import _ from 'lodash';
import dayjs from '@/core/plugins/initDayjs.js';
import { allowsFunctionalCookies } from '@/core/helpers/cookieHelpers.js';

const setExpiryForLocalStorage = _.once(() => {
    const localStoragePrototype = Object.getPrototypeOf(localStorage);
    const originalSetItemFunction = localStoragePrototype.setItem.bind(localStorage);
    localStoragePrototype.setItem = function setItem(key, data, expires = 31_536_000 /* 365 days */) {
        const expiresAt = dayjs().add(expires, 'seconds').toISOString();
        const item = {
            value: data,
            expiresAt,
        };
        return originalSetItemFunction(key, JSON.stringify(item));
    };

    const originalGetItemFunction = localStoragePrototype.getItem.bind(localStorage);
    localStoragePrototype.getItem = function getItem(key) {
        const item = originalGetItemFunction(key);
        if (item) {
            try {
                const parsed = JSON.parse(item);
                if (_.has(parsed, 'value') && _.has(parsed, 'expiresAt')) {
                    if (dayjs(parsed.expiresAt).isBefore(dayjs())) {
                        localStorage.removeItem(key);
                        return undefined;
                    }
                    return parsed.value;
                }
                return item;
            } catch (error) {
                return item;
            }
        }
        return item;
    };
});

export function clearExpiredStorage() {
    // Putting behind a timeout so it doesn't block the page load.
    return new Promise((resolve) => {
        setTimeout(() => {
            const keys = Object.keys(localStorage);
            keys.forEach((key) => {
                const value = localStorage[key];
                try {
                    const expiresAt = JSON.parse(value).expiresAt;
                    if (dayjs(expiresAt).isBefore(dayjs())) {
                        localStorage.removeItem(key);
                    }
                } catch (error) {
                    //
                }
            });
            resolve();
        }, 1);
    });
}

export function store(key, data, dataType = 'functional') {
    setExpiryForLocalStorage();
    if (dataType === 'functional' && allowsFunctionalCookies()) {
        localStorage.setItem(key, JSON.stringify(data));
    }
}

export function get(key, dataType = 'functional') {
    setExpiryForLocalStorage();
    if (dataType === 'functional' && allowsFunctionalCookies()) {
        const stored = localStorage.getItem(key);

        return Promise.resolve(stored ? JSON.parse(stored) : null);
    }
    return Promise.resolve(null);
}

export function clear(key) {
    localStorage.removeItem(key);
}
