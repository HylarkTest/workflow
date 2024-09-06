import SubsetValue from '@/components/product/SubsetValue.vue';

export default {
    components: {
        SubsetValue,
    },
    props: {
        page: {
            type: [Object, null],
            default: null,
        },
    },
    computed: {
        mappingObj() {
            return null; // Define in component
        },
        isSubset() {
            return this.markerFiltersLength || this.fieldFiltersLength;
        },
        fieldFilters() {
            return this.page?.fieldFilters;
        },
        fieldFiltersLength() {
            return this.fieldFilters?.length || 0;
        },
        markerFilters() {
            return this.page?.markerFilters;
        },
        markerFiltersLength() {
            return this.markerFilters?.length || 0;
        },
        mappingName() {
            return this.mappingObj?.name;
        },
        mappingFields() {
            return this.mappingObj?.fields;
        },
        filterName() {
            if (this.markerFiltersLength) {
                return this.markerGroupName;
            }
            if (this.fieldFiltersLength) {
                return this.fieldName;
            }
            return null;
        },
        filteredField() {
            if (this.fieldFiltersLength) {
                return this.mappingFields?.find((field) => {
                    return this.fieldFilters[0].fieldId === field.id;
                });
            }
            return null;
        },
        fieldName() {
            return this.filteredField?.name;
        },
        fieldMatch() {
            if (this.fieldFiltersLength) {
                return this.fieldFilters[0].match;
            }
            return null;
        },
        markerGroupName() {
            return this.markerGroupWithValue?.name;
        },
        markerWithValue() {
            if (this.markerFiltersLength) {
                return this.findMarker(this.markerGroupWithValue.items);
            }
            return null;
        },
        markerGroups() {
            return _.map(this.mappingObj?.markerGroups, 'group');
        },
        markerGroupWithValue() {
            if (this.markerGroups?.length && this.markerFiltersLength) {
                return this.markerGroups.find((group) => {
                    return this.findMarker(group.items);
                });
            }
            return null;
        },
        fieldValue() {
            if (this.fieldFiltersLength) {
                const field = this.filteredField;
                const type = field.type;
                if (type === 'BOOLEAN') {
                    return this.$t(`common.${this.fieldMatch}`);
                }
                // That means type is 'SELECT'
                return field.options.valueOptions[this.fieldMatch];
            }
            return null;
        },
        operator() {
            if (this.markerFiltersLength) {
                return _.camelCase(this.markerFilters[0].operator);
            }
            if (this.fieldFiltersLength) {
                return _.camelCase(this.fieldFilters[0].operator);
            }
            return null;
        },
    },
    methods: {
        findMarker(markers) {
            const filter = this.markerFilters[0].markerId;
            return _.find(markers, { id: filter });
        },
    },
};
