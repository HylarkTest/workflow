import { arrRemove } from '@/core/utils.js';

export default {
    props: {
        filterables: {
            type: Array,
            required: true,
        },
    },
    data() {
        return {
            filterEmitName: 'update:discreteFilters',
            filterEmitBase: 'discreteFilters',
            getKeyPrefix: '',
            filterView: {},
        };
    },
    computed: {
        discreteFiltersArray() {
            return _(this.discreteFilters).flatMap((filters) => {
                return filters?.map((filter) => filter);
            }).compact().value();
        },
        optionsLength() {
            return this.filterables.length;
        },
        formattedFilterables() {
            return this.filterables.map((filter) => {
                return {
                    group: this.$t(filter.namePath),
                    val: filter.val,
                    options: filter.options,
                };
            });
        },
        formattedDiscreteFilters() {
            const discreteFilters = [];
            _.forEach(this.discreteFilters, (filters) => {
                filters.forEach((filter) => {
                    discreteFilters.push(filter.id);
                });
            });
            return discreteFilters;
        },
        discreteFiltersLength() {
            return this.discreteFiltersArray.length;
        },
        filtersSource() {
            return this[this.filterEmitBase];
        },
    },
    methods: {
        applyFilter({ value, group, page }) {
            const groupVal = group.val;
            const type = groupVal;
            const filter = value;
            const filterObj = {
                filterType: groupVal,
                filter,
                page,
            };
            const arr = _.get(this.filtersSource, groupVal, []);

            const hasAlready = !!(arr.find((record) => {
                return _.isEqual(record.filter, filter);
            }));

            if (!hasAlready) {
                this.$proxyEvent([...arr, filterObj], this[this.filterEmitBase], type, this.filterEmitName);
            }
        },
        removeFilter(filterObj, group) {
            // There are two cases here, one where a user is removed from the
            // list of active filters, and one where they are removed from the
            // dropdown.
            const typeVal = group.val;

            const type = typeVal;

            const filter = filterObj.filter ? filterObj.filter : filterObj;
            const arr = this.filtersSource[typeVal];

            const item = arr.find((record) => {
                return _.isEqual(record.filter, filter);
            });
            const newArr = arrRemove(arr, item);

            const sourceKeys = _.keys(this.filtersSource);

            if (!newArr.length && sourceKeys.length <= 1) {
                // The last one should set the filters to null rather than empty object
                this.clearFilters();
            } else if (newArr.length) {
                this.$proxyEvent(arrRemove(arr, item), this[this.filterEmitBase], type, this.filterEmitName);
            } else {
                this.$emit(this.filterEmitName, _.omit(this[this.filterEmitBase], type));
            }
        },
        clearFilters() {
            this.$proxyEvent(null, this[this.filterEmitBase], '', this.filterEmitName);
        },
    },
};
