export default {
    data() {
        return {
        };
    },
    computed: {
        mainFilter() {
            return this.filtersObj.filter;
        },
        discreteFilters() {
            return this.filtersObj.discreteFilters;
        },
        hasContentFilters() {
            return !!(this.filtersObj.freeText || this.discreteFilters);
        },
        hasActiveFilters() {
            return this.hasContentFilters || !!this.mainFilter;
        },
    },
};
