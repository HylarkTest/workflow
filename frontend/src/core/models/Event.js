import IntegratableListItem from '@/core/models/IntegratableListItem.js';
import dayjs from '@/core/plugins/initDayjs.js';
import { dateWithTz } from '@/core/repositories/preferencesRepository.js';

/**
 * @property {string} id
 * @property {number} priority
 * @property {string} name
 * @property {string} startAt
 * @property {string} endAt
 * @property {string} timezone
 * @property {string} description
 * @property {string} createdAt
 * @property {string} updatedAt
 * @property {number} recurrence.interval
 * @property {Array<string>} recurrence.byDay
 * @property {string} recurrence.frequency
 * @property {string} account.id
 * @property {string} account.provider
 * @property {string} calendar.id
 * @property {string} calendar.name
 * @property {string} calendar.color
 * @property {string} __typename
 */
export default class Event extends IntegratableListItem {
    get color() {
        return this.calendar.color;
    }

    isRecurringInstance() {
        return /([a-zA-Z1-9=]+)_(\d{8}T\d{6}Z)/.test(this.id);
    }

    get startWithTz() {
        return this.isAllDay
            ? dayjs.utc(this.date)
            : dateWithTz(this.date, this.timezone);
    }

    get endWithTz() {
        return this.isAllDay
            ? dayjs.utc(this.end)
            : dateWithTz(this.end, this.timezone);
    }
}
