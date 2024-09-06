import ListItem from '@/core/models/ListItem.js';

/**
 * @property {string} id
 * @property {string} name
 * @property {object} delta
 * @property {string} preview
 * @property {string} createdAt
 * @property {string} updatedAt
 * @property {string} notebook.id
 * @property {string} notebook.name
 * @property {string} notebook.color
 * @property {string} __typename
 */
export default class Note extends ListItem {
    get color() {
        return this.notebook.color;
    }
}
