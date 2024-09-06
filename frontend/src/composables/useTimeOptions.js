import { toRefs, computed } from 'vue';

import {
    is24Hours,
} from '@/core/repositories/preferencesRepository.js';

// forceTime, forceDate, and forceAllDay only take affect if:
// 1) the input value is NOT null, and
// 2) the input value does not contain the related value (DATE or TIME)
// This is because we want to allow the input value to be nullable,
// so these options are only used when a value DOES exist, just with "missing" parts.
const defaultTimeOptions = {
    minuteInterval: 5,
    forceTime: false,
    forceDate: false,
    forceAllDay: false,
};

export default (props) => {
    const {
        timeOptionsProp = { value: {} },
        isMicrosoftItem = { value: false },
    } = toRefs(props);

    const timeOptions = computed(() => {
        return {
            ...defaultTimeOptions,
            ...timeOptionsProp.value,
            is24Hours: is24Hours.value,
            allDayIndicator: isMicrosoftItem.value ? '00:00' : '23:59:59',
        };
    });

    return {
        timeOptions,
    };
};
