import { watch } from 'vue';

import useDateTime from '@/composables/useDateTime.js';
import useTimezone from '@/composables/useTimezone.js';
import useTimeOptions from '@/composables/useTimeOptions.js';

import {
    formatDateTime,
    convertDateFromTimezoneToUtc,
} from '@/core/dateTimeHelpers.js';

export default (props, context) => {
    const {
        modelValue: dateTime,
        modelValueTime: time,
        modelValueDate: modelValue,
        dateInUtcMode,
    } = useDateTime(props);

    const {
        timezone,
    } = useTimezone(props);

    const {
        timeOptions,
    } = useTimeOptions(props);

    const noConvert = (newDateTime, format) => {
        if (format === 'DATE_TIME') {
            return newDateTime.split(' ')[1] === timeOptions.value.allDayIndicator;
        }
        if (format === 'TIME') {
            return newDateTime === timeOptions.value.allDayIndicator;
        }
        // if (format === 'DATE')
        return true;
    };

    const updateModelValue = (dateValue) => {
        const date = formatDateTime(dateValue, 'DATE');

        let newDateTime = null;
        let newFormat = null;

        if (dateInUtcMode.value === 'DATE_TIME') {
            if (_.isNull(date)) {
                newDateTime = timeOptions.value.forceDate ? null : time.value;
                newFormat = timeOptions.value.forceDate ? 'DATE_TIME' : 'TIME';
            } else {
                newDateTime = `${date} ${time.value}`;
                newFormat = 'DATE_TIME';
            }
        } else if (dateInUtcMode.value === 'TIME') { // "date" param cannot be null if this is called
            newDateTime = `${date} ${time.value}`;
            newFormat = 'DATE_TIME';
        } else if (dateInUtcMode.value === 'DATE') {
            newDateTime = _.isNull(date) ? null : date;
            newFormat = 'DATE';
        } else if (_.isNull(dateTime.value)) { // "date" param cannot be null if this is called
            if (timeOptions.value.forceTime) {
                newDateTime = `${date} ${timeOptions.value.allDayIndicator}`;
                newFormat = 'DATE_TIME';
            } else {
                newDateTime = date;
                newFormat = 'DATE';
            }
        }

        let dateTimeVal = newDateTime;

        if (dateTimeVal && !noConvert(newDateTime, newFormat)) {
            dateTimeVal = convertDateFromTimezoneToUtc(newDateTime, timezone, newFormat);
        }
        context.emit('update:dateTime', dateTimeVal);
    };

    watch(() => timeOptions.value.forceAllDay, () => updateModelValue(dateTime.value));

    return {
        modelValue,
        updateModelValue,
        dateTime,
    };
};
