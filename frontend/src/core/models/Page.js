import Model from '@/core/models/Model.js';
import { instantiate } from '@/core/utils.js';
import SavedFilter from '@/core/models/SavedFilter.js';

/**
 * @property {string} id
 * @property {string} name
 * @property {string} createdAt
 * @property {string} updatedAt
 * @property {string} space.id
 * @property {string} space.name
 * @property {string} __typename
 */
export default class Page extends Model {
    get filter() {
        if (this.fieldFilters?.length) {
            const filter = this.fieldFilters[0];
            return {
                by: 'FIELD',
                fieldId: filter.fieldId,
                match: filter.operator,
                matchValue: filter.match,
            };
        }
        if (this.markerFilters?.length) {
            const filter = this.markerFilters[0];
            return {
                by: 'MARKER',
                match: filter.operator,
                matchValue: filter.markerId,
                context: filter.context,
            };
        }
        return null;
    }

    isListPage() {
        return [
            'TODOS',
            'CALENDAR',
            'DOCUMENTS',
            'PINBOARD',
            'NOTES',
            'LINKS',
        ].includes(this.type);
    }

    isRecordPage() {
        return [
            'ENTITY',
            'ENTITIES',
        ].includes(this.type);
    }

    get activeDefaultFilter() {
        const filterData = this.personalDefaultFilter
            || this.defaultFilter;
        return filterData && instantiate(filterData, SavedFilter);
    }

    get route() {
        const name = this.isRecordPage()
            ? 'page'
            : 'feature';
        return {
            name,
            params: {
                pageId: this.id,
            },
        };
    }

    get mostRecentItems() {
        const items = this.__typename === 'ListPage'
            ? this.recentFeatureItems
            : this.recentItems;
        return _.map(items?.edges, 'node');
    }
}
