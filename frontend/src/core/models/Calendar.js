import IntegratableList from '@/core/models/IntegratableList.js';

/**
 * @property {number} order
 * @property {boolean} isDefault
 * @property {boolean} isOwner
 * @property {boolean} isShared
 */
export default class Calendar extends IntegratableList {
    route() {
        return { name: 'calendar', params: { listId: this.id, providerId: this.account?.id } };
    }
}
