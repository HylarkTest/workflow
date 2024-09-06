import ListItem from '@/core/models/ListItem.js';

/**
 * @property {string} id
 * @property {string} name
 * @property {string} createdAt
 * @property {string} updatedAt
 * @property {string} pinboard.id
 * @property {string} pinboard.name
 * @property {string} pinboard.color
 * @property {string} __typename
 */
export default class Pin extends ListItem {
    get color() {
        return this.pinboard.color;
    }
}
