import List from '@/core/models/List.js';

/**
 * @property {number} order
 * @property {number} count
 * @property {boolean} isDefault
 */
export default class Drive extends List {
    route() {
        return { name: 'documents', params: { listId: this.id } };
    }
}
