import List from '@/core/models/List.js';

/**
 * @property {number} order
 * @property {number} count
 * @property {boolean} isDefault
 */
export default class Notebook extends List {
    route() {
        return { name: 'notes', params: { listId: this.id } };
    }
}
