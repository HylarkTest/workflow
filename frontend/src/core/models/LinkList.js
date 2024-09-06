import List from '@/core/models/List.js';

/**
 * @property {number} order
 * @property {number} count
 * @property {boolean} isDefault
 */
export default class LinkList extends List {
    route() {
        return { name: 'links', params: { listId: this.id } };
    }
}
