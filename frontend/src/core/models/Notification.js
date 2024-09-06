import dayjs from '@/core/plugins/initDayjs.js';
import Model from '@/core/models/Model.js';
import { lastSeenNotifications } from '@/core/repositories/preferencesRepository.js';

/**
 * @property {string} id
 * @property {string} header
 * @property {string} content
 * @property {string} image
 * @property {string} createdAt
 * @property {string} updatedAt
 * @property {string} clearedAt
 * @property {string} __typename
 */
export default class Notification extends Model {
    isNew() {
        if (this.clearedAt) {
            return false;
        }
        if (!lastSeenNotifications.value) {
            return true;
        }
        return dayjs(lastSeenNotifications.value).isBefore(dayjs(this.createdAt));
    }
}
