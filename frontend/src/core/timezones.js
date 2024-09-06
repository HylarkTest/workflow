import _ from 'lodash';
import timezones from '@/../timezones.json';
import dayjs from '@/core/plugins/initDayjs.js';

export default function getTimezones() {
    const date = new Date();

    return timezones.timezones.map((timeZone) => {
        const readableTimeZone = timeZone.replaceAll('_', ' ');
        try {
            const timeZoneName = new Intl.DateTimeFormat('en-GB', {
                timeZone,
                timeZoneName: 'long',
            }).format(date).substring(12);
            const timeZoneAcronym = _(timeZoneName).words().map((item) => item[0]).join('')
                .toUpperCase();
            const timeZoneOffset = new Intl.DateTimeFormat('en-GB', {
                timeZone,
                timeZoneName: 'longOffset',
            }).format(date).substring(12);
            return {
                acronym: timeZoneAcronym,
                long: `(${timeZoneOffset}) ${readableTimeZone} (${timeZoneAcronym})`,
                short: readableTimeZone,
                value: timeZone,
            };
        } catch (e) {
            if (e instanceof RangeError) {
                return null;
            }
            throw e;
        }
    }).filter(_.identity);
}

export function guessTimezone() {
    const tz = dayjs.tz.guess();
    return timezones.links[tz] || tz;
}
