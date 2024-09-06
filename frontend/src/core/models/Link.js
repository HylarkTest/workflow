import ListItem from '@/core/models/ListItem.js';

/**
 * @property {string} id
 * @property {string} name
 * @property {object} delta
 * @property {string} preview
 * @property {string} createdAt
 * @property {string} updatedAt
 * @property {string} linkList.id
 * @property {string} linkList.name
 * @property {string} linkList.color
 * @property {string} __typename
 */
export default class Link extends ListItem {
    get color() {
        return this.linkList.color;
    }
}
