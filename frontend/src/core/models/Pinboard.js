import List from '@/core/models/List.js';

/**
 * @property {number} order
 * @property {number} count
 * @property {boolean} isDefault
 */
export default class Pinboard extends List {
    route() {
        return { name: 'pinboard', params: { listId: this.id } };
    }
}
