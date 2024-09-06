import MARKER_GROUPS from '@/graphql/markers/queries/MarkerGroups.gql';
import { initializeMarkers } from '@/core/repositories/markerRepository.js';

const allowedFieldTypes = [
    'BOOLEAN',
    'SELECT',
];

export default {
    apollo: {
        markerGroups: {
            query: MARKER_GROUPS,
            variables() {
                return { usedByMappings: this.mapping ? [this.mapping.id] : [] };
            },
            skip() {
                return !this.mapping;
            },
            update: initializeMarkers,
        },
    },
    computed: {
        isLoadingMarkers() {
            return this.$apollo.queries.markerGroups.loading;
        },
        hasFilterOptions() {
            return this.filterOptions.length;
        },
        hasMultipleOptions() {
            return this.hasFilterOptions > 1;
        },
        filterOptions() {
            const options = [];
            if (this.hasMarkers) {
                options.push({
                    val: 'MARKER',
                    name: 'Marker',
                });
            }
            if (this.hasFields) {
                options.push({
                    val: 'FIELD',
                    name: 'Field',
                });
            }
            return options;
        },
        fields() {
            return this.mapping?.fields || [];
        },
        fieldsOnMapping() {
            return _.has(this.mapping, 'fields');
        },
        filteredFields() {
            return this.fields.filter((field) => {
                return allowedFieldTypes.includes(field.type);
            });
        },
        markerGroupsArr() {
            return this.markerGroups?.markerGroups;
        },
        hasMarkers() {
            if (this.markerGroupsArr) {
                return this.markerGroupsArr.some((group) => {
                    return group.markerCount > 0;
                });
            }
            return false;
        },
        markerGroupsLength() {
            return this.markerGroupsArr?.length;
        },
        hasFields() {
            return this.filteredFields.length;
        },
    },
    created() {
        this.allowedFieldTypes = allowedFieldTypes;
    },
};
