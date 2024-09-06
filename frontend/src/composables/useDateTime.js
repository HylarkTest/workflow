import { toRefs, computed } from 'vue';

import useTimeOptions from '@/composables/useTimeOptions.js';
import useTimezone from '@/composables/useTimezone.js';

import {
    formatDateTime,
    getStringMode,
    newDate,
    convertDateFromUtcToTimezone,
} from '@/core/dateTimeHelpers.js';

export default (props) => {
    const {
        dateTime,
    } = toRefs(props);

    const { timezone } = useTimezone(props);
    const { timeOptions } = useTimeOptions(props);

    const currentUtc = computed(() => newDate('UTC', 'DATE_TIME'));
    const currentDateTime = computed(() => newDate(timezone, 'DATE_TIME'));
    const currentDate = computed(() => newDate(timezone, 'DATE'));
    const currentTime = computed(() => newDate(timezone, 'TIME'));

    const dateTimeSimplified = computed(() => formatDateTime(dateTime.value, null, 'UTC'));

    const inputMode = computed(() => getStringMode(dateTimeSimplified.value));

    const dateInUtc = computed(() => {
        if (inputMode.value === 'DATE_TIME') {
            if (timeOptions.value.forceAllDay) {
                return `${dateTimeSimplified.value.split(' ')[0]} ${timeOptions.value.allDayIndicator}`;
            }
            return dateTimeSimplified.value;
        }

        if (inputMode.value === 'DATE') {
            if (timeOptions.value.forceAllDay || timeOptions.value.forceTime) {
                // only force a value if dateTime is not null, and TIME does not exist
                return `${dateTimeSimplified.value} ${timeOptions.value.allDayIndicator}`;
            }
            return dateTimeSimplified.value;
        }

        if (inputMode.value === 'TIME') {
            if (timeOptions.value.forceAllDay) {
                return timeOptions.value.allDayIndicator;
            }
            if (timeOptions.value.forceDate) {
                // only force a value if dateTime is not null, and DATE does not exist
                return `${currentDate.value} ${dateTimeSimplified.value}`;
            }
            return dateTimeSimplified.value;
        }

        return null;
    });

    // dateInUtc may have a different format fom inputMode based on timeOptions
    const dateInUtcMode = computed(() => getStringMode(dateInUtc.value));

    const isAllDay = computed(() => {
        if (dateInUtcMode.value === 'DATE_TIME') {
            return dateInUtc.value.split(' ')[1] === timeOptions.value.allDayIndicator;
        }
        if (dateInUtcMode.value === 'TIME') {
            return dateInUtc.value === timeOptions.value.allDayIndicator;
        }
        return false;
    });

    const modelValue = computed(() => {
        if (_.isNull(dateInUtc.value)) {
            return null;
        }
        if (isAllDay.value) {
            return dateInUtc.value;
        }
        return convertDateFromUtcToTimezone(dateInUtc.value, timezone, dateInUtcMode.value);
    });

    // modelValueDate & modelValueTime are here so that useDateInput and useTimeInput can access both
    // without causing an infinite loop on setup.
    // They are imported into their respective composable file and reassigned to modelValue for export.
    const modelValueDate = computed(() => {
        if (_.isNull(modelValue.value)) {
            return null;
        }
        if (dateInUtcMode.value === 'DATE') {
            return modelValue.value;
        }
        if (dateInUtcMode.value === 'DATE_TIME') {
            return modelValue.value.split(' ')[0];
        }
        // if (dateInUtcMode.value === 'TIME')
        return null;
    });

    const modelValueTime = computed(() => {
        if (_.isNull(modelValue.value)) {
            return null;
        }
        if (dateInUtcMode.value === 'TIME') {
            return modelValue.value;
        }
        if (dateInUtcMode.value === 'DATE_TIME') {
            return modelValue.value.split(' ')[1];
        }
        // if (dateInUtcMode.value === 'DATE')
        return null;
    });

    return {
        currentUtc,
        currentDateTime,
        currentDate,
        currentTime,

        inputMode,
        dateInUtcMode,
        isAllDay,
        modelValue,

        modelValueDate,
        modelValueTime,
    };
};
