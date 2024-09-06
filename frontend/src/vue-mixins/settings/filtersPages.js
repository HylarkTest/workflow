export default {
    data() {
        return {
            filters: {
                sortOrder: {
                    value: 'name',
                    name: 'Name',
                    direction: 'ASC',
                },
            },
        };
    },
    computed: {
        pageFilters() {
            const type = _.map(this.filters.activeFilters?.type, 'filter.id');
            return {
                pageName: this.filters.freeText,
                pageType: type.length ? type : null,
                pageCreatedBy: _.map(this.filters.activeFilters?.createdBy, 'filter.id'),
                pageLastUpdatedBy: _.map(this.filters.activeFilters?.updatedBy, 'filter.id'),
                pageOrderBy: [
                    {
                        column: this.filters.sortOrder.value,
                        order: this.filters.sortOrder.direction,
                    },
                    {
                        column: 'id',
                        order: this.filters.sortOrder.direction,
                    },
                ],
            };
        },
    },
};
