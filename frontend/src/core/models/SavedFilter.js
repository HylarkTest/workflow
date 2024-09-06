import Model from '@/core/models/Model.js';
import { convertApiFiltersToLocal } from '@/core/helpers/filterConverter.js';

/**
 * @property {string} id
 * @property {string} name
 * @property {array} orderBy
 * @property {array} filters
 * @property {string} group
 * @property {string} createdAt
 * @property {string} updatedAt
 * @property {string} __typename
 */
export default class SavedFilter extends Model {
    toLocalFilters(filterables) {
        return convertApiFiltersToLocal(this, filterables);
    }
}
