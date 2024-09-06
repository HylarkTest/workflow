import {
    computed, readonly, ref, watch,
} from 'vue';
import axios from 'axios';
import { onAuthenticated, onLogout } from '@/core/auth.js';
import dayjs from '@/core/plugins/initDayjs.js';
import { get, store } from '@/core/localStorage.js';
import { guessTimezone } from '@/core/timezones.js';
import { updateProfile } from '@/core/repositories/baseRepository.js';
import { createApolloForm } from '@/core/plugins/formlaPlugin.js';
import { defaultApolloClient } from '@/http/apollo/defaultApolloClient.js';

const defaultMoneyFormat = {
    decimal: '.',
    separator: ' ',
};

const userPreferencesRef = ref();

function setPreferencesWithDefaults(preferences) {
    userPreferencesRef.value = {
        ...preferences,
        colorMode: preferences.colorMode || 'LIGHT',
        dateFormat: preferences.dateFormat || 'DMY',
        weekdayStart: preferences.weekdayStart || 1,
        timeFormat: preferences.timeFormat || '12',
        timezone: preferences.timezone || guessTimezone(),
        moneyFormat: preferences.moneyFormat || defaultMoneyFormat,
    };
}

const preferencesCacheKey = 'user-preferences';
get(preferencesCacheKey).then((cachedPreferences) => {
    setPreferencesWithDefaults(cachedPreferences || {});
});

watch(userPreferencesRef, (newPreferences) => {
    store(preferencesCacheKey, newPreferences);
});

let loadingRequest;

let preferencesLoaded = false;

function fetchUserPreferences() {
    if (!loadingRequest && !preferencesLoaded) {
        loadingRequest = axios.get('/preferences').then((response) => {
            setPreferencesWithDefaults(response.data);
        }).then(() => {
            preferencesLoaded = true;
        }).finally(() => {
            loadingRequest = null;
        });
    }

    return loadingRequest;
}

export default fetchUserPreferences;

onAuthenticated(fetchUserPreferences);
onLogout(() => {
    userPreferencesRef.value = null;
});

async function updatePreference(field, value) {
    const response = await axios.post('/preferences', {
        [field]: value,
    });

    setPreferencesWithDefaults(response.data);

    return response;
}

export function updateColorMode(colorMode) {
    return updatePreference('colorMode', colorMode);
}

export function updateWeekdayStart(weekStart) {
    return updatePreference('weekdayStart', weekStart);
}

export function updateTimezone(timezone) {
    return updatePreference('timezone', timezone);
}

export function updateDateFormat(format) {
    return updatePreference('dateFormat', format);
}

export function updateTimeFormat(format) {
    return updatePreference('timeFormat', format);
}

export function updateMoneyFormat(format) {
    return updatePreference('moneyFormat', format);
}

export function updateActiveNotifications(activeNotifications) {
    return updatePreference('activeAppNotifications', activeNotifications);
}

export function setLastSeenNotificationsToNow() {
    userPreferencesRef.value.lastSeenNotifications = (new Date()).toISOString();
}

export async function updateShortcuts(shortcuts) {
    return updateProfile(createApolloForm(defaultApolloClient(), {
        preferences: { shortcuts },
    }));
}

export async function updateWidgets(form) {
    return updateProfile(createApolloForm(defaultApolloClient(), {
        preferences: {
            widgets: form.getData(),
        },
    }));
}

export function dateInUtc(date) {
    let usedDate;
    if (dayjs.isDayjs(date)) {
        usedDate = date;
    } else {
        usedDate = dayjs(date);
    }
    return usedDate.tz('utc');
}

export function dateWithTz(date, timezone = null) {
    let usedTimezone = timezone;
    if (!timezone) {
        usedTimezone = userPreferencesRef.value?.timezone;
    }
    let usedDate;
    if (dayjs.isDayjs(date)) {
        usedDate = date;
    } else {
        usedDate = dayjs(date);
    }
    const utcDate = dateInUtc(usedDate);
    return utcDate.tz(usedTimezone);
}

export function inUsersTimezone(date) {
    return dayjs.utc(date).tz(userPreferencesRef.value?.timezone);
}

export function timeDayjsFormat() {
    const format = userPreferencesRef.value?.timeFormat;
    if (format === '12') {
        return 'h:mm A';
    }
    return 'H:mm';
}

export function dateDayjsFormat() {
    const format = userPreferencesRef.value?.dateFormat;
    if (format === 'YMD') {
        return 'YYYY/MM/DD';
    }
    if (format === 'MDY') {
        return 'MM/DD/YYYY';
    }
    return 'DD/MM/YYYY';
}

export function dateInFormat(date) {
    const dateFormat = dateDayjsFormat();
    return date.format(dateFormat);
}

export function formattedTime(date) {
    const usedDate = dateWithTz(date);
    const timeFormat = timeDayjsFormat();
    return usedDate.format(timeFormat);
}

export const colorMode = computed(() => userPreferencesRef.value?.colorMode || 'LIGHT');
export const dateFormat = computed(() => userPreferencesRef.value?.dateFormat || 'DMY');
export const weekdayStart = computed(() => (userPreferencesRef.value?.weekdayStart || 1));
export const timeFormat = computed(() => userPreferencesRef.value?.timeFormat);
export const moneyFormat = computed(() => userPreferencesRef.value?.moneyFormat);
export const is24Hours = computed(() => timeFormat.value === '24');
export const activeAppNotifications = computed(() => userPreferencesRef.value?.activeAppNotifications);
export const lastSeenNotifications = computed(() => userPreferencesRef.value?.lastSeenNotifications);
export const userPreferences = readonly(userPreferencesRef);
export const timezone = computed(() => userPreferencesRef.value?.timezone);

// Was causing issues, but might be useful in the future.
// watchEffect(() => {
//     dayjs.tz.setDefault(timezone.value || dayjs.tz.guess());
// });
