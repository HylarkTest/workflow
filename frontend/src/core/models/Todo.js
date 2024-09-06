import IntegratableListItem from '@/core/models/IntegratableListItem.js';

/**
 * @property {string} id
 * @property {number} priority
 * @property {string} name
 * @property {string} completedAt
 * @property {string} dueBy
 * @property {string} description
 * @property {string} createdAt
 * @property {string} updatedAt
 * @property {number} order
 * @property {number} recurrence.interval
 * @property {Array<string>} recurrence.byDay
 * @property {string} recurrence.frequency
 * @property {string} account.id
 * @property {string} account.provider
 * @property {string} list.id
 * @property {string} list.name
 * @property {string} list.color
 * @property {string} __typename
 */
export default class Todo extends IntegratableListItem {
    hasPriority() {
        return !this.isGoogleItem();
    }

    allowedPriorities() {
        if (this.isMicrosoftItem()) {
            return [0, 1, 5, 9];
        }
        return [0, 1, 3, 5, 9];
    }

    isCompleted() {
        return !!this.completedAt;
    }

    dueTime() {
        return this.dueBy?.split('T')[1];
    }

    noDueTime() {
        return this.dueTime()?.includes('23:59');
    }

    get date() {
        return this.dueBy;
    }

    get end() {
        return this.dueBy;
    }

    get isAllDay() {
        return this.noDueTime();
    }

    get color() {
        return this.list.color;
    }
}
