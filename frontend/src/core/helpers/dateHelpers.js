import { reactive } from 'vue';

import dayjs from '@/core/plugins/initDayjs.js';

import useDateTime from '@/composables/useDateTime.js';

import { $t } from '@/i18n.js';

import { dateFormats, getDateObject, formatDateTime } from '@/core/dateTimeHelpers.js';

export function isMidnight(dateObj) {
    return dateObj.hour() === 0 && dateObj.minute() === 0;
}

export function getDateWithoutMidnight(date) {
    // Input String or Date object
    let dateObj = date;
    const isDateString = _.isString(date);
    if (isDateString) {
        dateObj = dayjs(date);
    }
    if (isMidnight(dateObj)) {
        dateObj = dateObj.subtract(1, 'minute');
    }
    // Output has same type as input, String or Date object
    if (isDateString) {
        return dateObj.format('YYYY-MM-DD HH:mm:ss');
    }
    return dateObj;
}

export function getDateAfterPeriod(string) {
    let dateObj = dayjs().utc().endOf('day');

    if (string === 'TOMORROW') {
        dateObj = dateObj.add(1, 'day');
    } else if (string === 'NEXT_WEEK') {
        dateObj = dateObj.add(1, 'week');
    }
    dateObj = dateObj.format(dateFormats.DATE_TIME);
    return dateObj;
}

export function fromNowWithToday(date) {
    // Date argument formatted as 'YYYY-MM-DD'
    const today = dayjs().format('YYYY-MM-DD');
    if (date === today) {
        return 'Today';
    }
    return dayjs(date).fromNow();
}

export function getBasicDateLabels(dateObj, defaultFormat = null) {
    // dateObj is dayJs object, can later expand function to deal with date strings
    if (dateObj.isToday()) {
        return $t('labels.today');
    }
    if (dateObj.isTomorrow()) {
        return $t('labels.tomorrow');
    }
    if (dateObj.isYesterday()) {
        return $t('labels.yesterday');
    }
    return defaultFormat;
}

export function lastDayOfMonth(year, month) {
    return new Date(year, month + 1, 0);
}

export function lastDayOfMonthDate(year, month) {
    return lastDayOfMonth(year, month).getDate();
}

export function isLastDayOfMonth(year, month, day) {
    const lastDay = lastDayOfMonthDate(year, month);

    return lastDay === day;
}

export function isFirstDayOfMonth(day) {
    return day === 1;
}

export function dayPositionInWeek(year, month, day, weekStart = 0) {
    const original = new Date(year, month, day).getDay();
    return (original + (7 - weekStart)) % 7;
}

export function getEventFullDateWithTz(date, isUtcOnly, timezone = null) {
    const utc = dayjs.tz(date, 'utc');
    if (isUtcOnly) {
        return utc;
    }
    return utils.dateWithTz(utc, timezone);
}

function hasOverlap(s0, e0, s1, e1, inclusive = true) {
    if (inclusive) {
        return !(s0 >= e1 || e0 <= s1);
    }
    return !(s0 > e1 || e0 < s1);
}

function setColumnCount(groups) {
    groups.forEach((group) => {
        group.visuals.forEach((groupVisual) => {
            // eslint-disable-next-line no-param-reassign
            groupVisual.columnCount = groups.length;
        });
    });
}

function getOpenGroup(groups, start, end, inclusive = true) {
    for (let i = 0; i < groups.length; i += 1) {
        const group = groups[i];
        let intersected = false;

        if (hasOverlap(start, end, group.start, group.end, inclusive)) {
            for (let k = 0; k < group.visuals.length; k += 1) {
                const groupVisual = group.visuals[k];
                const groupStart = groupVisual.start;
                const groupEnd = groupVisual.end;

                if (hasOverlap(start, end, groupStart, groupEnd, inclusive)) {
                    intersected = true;
                    break;
                }
            }
        }

        if (!intersected) {
            return i;
        }
    }

    return -1;
}

export function eventPositioning(events, splitEventsCb = null, formatPrecision = 'minutes') {
    const inclusive = formatPrecision === 'minutes';

    // Alright, complicated logic here. We need to get the events to
    // be positioned in a very specific way depending on the other
    // events in the day that may or may not overlap.

    // To begin with we create an object that will be populated with the
    // positioning information of each event, including how wide the
    // event is and which column it should be placed in.
    const visuals = events.flatMap((event) => {
        // convert Date object into DATE_TIME string in the user's timezone
        const { modelValue: startDateTime } = useDateTime(reactive({ dateTime: event.date }));
        const { modelValue: endDateTime } = useDateTime(reactive({ dateTime: event.end }));

        const visual = {
            event,
            start: getDateObject(startDateTime.value),
            end: getDateObject(endDateTime.value),
            columnCount: 0,
            column: 0,
        };

        const splitVisuals = splitEventsCb ? splitEventsCb(visual) : [visual];
        const format = formatPrecision === 'minutes' ? 'YYYYMMDDHHmm' : 'YYYYMMDD';

        return splitVisuals.map((eventVisual) => ({
            ...eventVisual,
            start: parseInt(formatDateTime(eventVisual.start, format), 10),
            end: parseInt(formatDateTime(eventVisual.end, format), 10),
        }));
    });

    // Here we define an object to keep track of the events that overlap
    // with each other.
    const handler = {
        groups: [],
        min: -1,
        max: -1,
        reset: () => {
            handler.groups = [];
            handler.min = -1;
            handler.max = -1;
        },
    };

    // We first sort the events by there start time and end time. They
    // should already be sorted coming in from the API, but we do it
    // again here, just to be safe.
    visuals.sort((a, b) => {
        return Math.max(0, a.start) - Math.max(0, b.start)
            || (b.end - a.end);
    });

    visuals.forEach((visual) => {
        const { start, end } = visual;

        // So here's where it gets confusing. The handler `groups` array
        // is an array of arrays of events. Each array relates to a
        // column that will be displayed in the calendar.
        // If the current event in the loop does not overlap with any
        // of the events in the handler then we are done with the
        // previous group. Se we can finalize the positioning of all the
        // events so far, and reset the handler for the next events.
        if (handler.groups.length > 0 && !hasOverlap(start, end, handler.min, handler.max, inclusive)) {
            setColumnCount(handler.groups);
            handler.reset();
        }

        // Here we get the index of the events array that does not
        // currently have an event intersecting with the current event
        // in the loop. Once we have that we can add the current event
        // to that array, to be the next one in the column.
        let targetGroup = getOpenGroup(handler.groups, start, end, inclusive);

        // If there is no group that doesn't intersect with the current
        // event, we create a new one.
        if (targetGroup === -1) {
            targetGroup = handler.groups.length;

            handler.groups.push({ start, end, visuals: [] });
        }

        // We then add the current event in the loop to the open group.
        // Setting the min and max times of the group for easy
        // comparison later on in the loop.
        const target = handler.groups[targetGroup];
        target.visuals.push(visual);
        target.start = Math.min(target.start, start);
        target.end = Math.max(target.end, end);

        // eslint-disable-next-line no-param-reassign
        visual.column = targetGroup;

        if (handler.min === -1) {
            handler.min = start;
            handler.max = end;
        } else {
            handler.min = Math.min(handler.min, start);
            handler.max = Math.max(handler.max, end);
        }
    });

    // Once we have gone through all the events, we can set the column
    // count of the events that haven't been processed yet.
    setColumnCount(handler.groups);

    handler.reset();

    // Finally we can loop through the events now with the known column
    // count, and calculate the width and positioning of each one.
    visuals.forEach((visual) => {
        // eslint-disable-next-line no-param-reassign
        visual.left = (visual.column * 100) / visual.columnCount;
        // eslint-disable-next-line no-param-reassign
        visual.width = 100 / visual.columnCount;
    });

    return visuals;
}
