import { computed } from 'vue';

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
        modelValueDate: date,
        modelValueTime: modelValue,
        dateInUtcMode,
        currentDate,
    } = useDateTime(props);

    const {
        timezone,
    } = useTimezone(props);

    const {
        timeOptions,
    } = useTimeOptions(props);

    const omitTime = computed(() => modelValue.value === timeOptions.value.allDayIndicator);

    const updateModelValue = (newTime) => {
        // 1) Calling this function implies that "forceAllDay" is false (the time input should be deactivated or hidden)
        // 2) Similarly, if "time" param is null, that implies "forceTime" is false
        // 3) Updating time means we are ALWAYS interested in converting timezones (never "timezoneless" for allDay)
        // 4) dateInTimezone, and therefor modelValue, are already modified by forceTime/forceDate/forceAllDay.
        // This means we only need to check forceTime if "time" param is null (default to allDayIndicator)
        // const defaultToAllDayIndicator = _.isNull(time) && forceTime;
        // const time = defaultToAllDayIndicator ? allDayIndicator : time;
        const time = formatDateTime(newTime, 'TIME', timezone);

        let inUtc = null;

        if (dateInUtcMode.value === 'DATE_TIME') {
            if (_.isNull(time)) {
                const dateTimeWithDefaultTime = `${date.value} ${timeOptions.value.allDayIndicator}`;
                // use user's actual date rather than UTC's
                inUtc = dateTimeWithDefaultTime;
            } else {
                const newDateTime = `${date.value} ${time}`;
                inUtc = convertDateFromTimezoneToUtc(newDateTime, timezone, 'DATE_TIME');
            }
        } else if (dateInUtcMode.value === 'DATE') {
            // "time" param cannot be null if this is called
            const newDateTime = `${date.value} ${time}`;
            inUtc = convertDateFromTimezoneToUtc(newDateTime, timezone, 'DATE_TIME');
        } else if (dateInUtcMode.value === 'TIME') {
            // if current value does not have a date, "time" is nullable
            inUtc = _.isNull(time) ? null : convertDateFromTimezoneToUtc(time, timezone, 'TIME');
        } else if (_.isNull(dateTime.value)) {
            // "time" param cannot be null if this is called
            if (timeOptions.value.forceDate) {
                const newDateTime = `${currentDate.value} ${time}`;
                inUtc = convertDateFromTimezoneToUtc(newDateTime, timezone, 'DATE_TIME');
            } else {
                inUtc = convertDateFromTimezoneToUtc(time, timezone, 'TIME');
            }
        }

        context.emit('update:dateTime', inUtc);
    };

    return {
        omitTime,
        modelValue,
        updateModelValue,
    };
};
