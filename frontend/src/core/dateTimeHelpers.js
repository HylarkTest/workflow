import _ from 'lodash';
import dayjs from '@/core/plugins/initDayjs.js';
import { $t } from '@/i18n.js';

export const dateFormats = {
    DATE_TIME: 'YYYY-MM-DD HH:mm:ss',
    DATE: 'YYYY-MM-DD',
    TIME: 'HH:mm:ss',
};

export const validStringStructures = {
    DATE_TIME: ['YYYY-MM-DD HH:mm:ss', 'YYYY-MM-DD HH:mm'],
    DATE: ['YYYY-MM-DD'],
    TIME: ['HH:mm:ss', 'HH:mm'],
};

// VALIDATION

export function isValidObject(date) {
    return _.isObject(date) && dayjs.isDayjs(date);
}

export function isValidISO(date) {
    return _.isString(date) && date.includes('T') && dayjs(date).isValid();
}

export function isValidString(date) {
    return _.isString(date) && !date.includes('T') && dayjs(date).isValid();
}

export function isValidDate(date) {
    return isValidObject(date) || isValidISO(date) || isValidString(date);
}

export function isValidStringStructure(dateTime, structureType) {
    if (!_.isString(dateTime)) {
        return false;
    }
    const validStrictures = validStringStructures[structureType];
    return validStrictures.some((structure) => dayjs(dateTime, structure, true).isValid());
}

export function isValidStringTimeOnly(time) {
    return isValidStringStructure(time, 'TIME');
}

export function isValidStringDateOnly(date) {
    return isValidStringStructure(date, 'DATE');
}

export function isValidStringDateTime(dateTime) {
    return isValidStringStructure(dateTime, 'DATE_TIME');
}

export function getStringMode(dateTime) {
    if (isValidStringDateTime(dateTime)) {
        return 'DATE_TIME';
    }
    if (isValidStringDateOnly(dateTime)) {
        return 'DATE';
    }
    if (isValidStringTimeOnly(dateTime)) {
        return 'TIME';
    }
    return null;
}

// FORMATTING

export function getDateObject(date) {
    if (isValidObject(date)) {
        return date;
    }
    if (isValidISO(date)) {
        return dayjs.utc(date);
    }
    if (isValidStringDateOnly(date)) {
        return dayjs(date, dateFormats.DATE);
    }
    if (isValidStringTimeOnly(date)) {
        return dayjs(date, dateFormats.TIME);
    }
    if (isValidString(date)) {
        return dayjs(date);
    }
    return null;
}

export function formatDateTime(dateTime, format = null, timezone = 'UTC') {
    const formatType = dateFormats[format] || format;

    if (isValidObject(dateTime)) {
        return dateTime.tz(timezone).format(formatType || dateFormats.DATE_TIME);
    }
    if (isValidISO(dateTime)) {
        return dayjs(dateTime).tz(timezone).format(formatType || dateFormats.DATE_TIME);
    }

    if (isValidStringTimeOnly(dateTime)) {
        return dayjs(dateTime, 'HH:mm:ss').format(formatType || dateFormats.TIME);
    }
    if (isValidStringDateOnly(dateTime)) {
        return dayjs(dateTime, 'YYYY-MM-DD').format(formatType || dateFormats.DATE);
    }
    if (isValidStringDateTime(dateTime)) {
        return dayjs(dateTime).format(formatType || dateFormats.DATE_TIME);
    }

    return null;
}

// TIMEZONE CONVERSION

export function convertTimezones(dateOrTime, from, to, format) {
    let fullDateFrom = dateOrTime;
    if (format === 'TIME' && isValidStringTimeOnly(dateOrTime)) {
        const today = dayjs().tz(from).format('YYYY-MM-DD');
        fullDateFrom = `${today} ${dateOrTime}`;
    }
    const dateObjTo = dayjs.tz(fullDateFrom, from).tz(to);
    return formatDateTime(dateObjTo, format, to);
}

export function convertDateFromUtcToTimezone(dateOrTime, timezone, format) {
    return convertTimezones(dateOrTime, 'UTC', timezone, format);
}

export function convertDateFromTimezoneToUtc(dateOrTime, timezone, format) {
    return convertTimezones(dateOrTime, timezone, 'UTC', format);
}

export function newDate(timezone, format) {
    return convertDateFromUtcToTimezone(dayjs(), timezone, format);
}

// LABELS

export function isToday(date) {
    return getDateObject(date).isToday();
}

export function isTomorrow(date) {
    return getDateObject(date).isTomorrow();
}

export function isYesterday(date) {
    return getDateObject(date).isYesterday();
}

export function getBasicDateLabels(date) {
    if (isToday(date)) {
        return $t('labels.today');
    }
    if (isTomorrow(date)) {
        return $t('labels.tomorrow');
    }
    if (isYesterday(date)) {
        return $t('labels.yesterday');
    }
    return null;
}
