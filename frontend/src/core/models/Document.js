import ListItem from '@/core/models/ListItem.js';

/**
 * @property {string} id
 * @property {string} name
 * @property {object} delta
 * @property {string} preview
 * @property {string} createdAt
 * @property {string} updatedAt
 * @property {string} drive.id
 * @property {string} drive.name
 * @property {string} drive.color
 * @property {string} __typename
 */
export default class Document extends ListItem {
    get color() {
        return this.drive.color;
    }
}
