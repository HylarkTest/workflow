export function convertLocalFiltersToApiFilters(localFilters) {
    const allFilters = {
        orderBy: [{
            field: localFilters.sortOrder.value,
            direction: localFilters.sortOrder.direction,
        }],
        group: null,
        filters: [],
    };

    if (localFilters.currentGroup) {
        allFilters.group = localFilters.currentGroup;
    }

    const filters = [];

    _(localFilters.discreteFilters?.MARKERS).groupBy('page.context')
        .forEach((markers, context) => {
            filters.push({
                boolean: 'OR',
                markers: markers.map(({ filter }) => ({
                    markerId: filter.id,
                    context,
                    operator: 'IS',
                })),
            });
        });

    _(localFilters.discreteFilters?.FIELDS).groupBy('filter.field.id')
        .forEach((fields) => {
            filters.push({
                boolean: 'OR',
                fields: fields.map(({ filter }) => ({
                    fieldId: filter.field.id,
                    match: filter.value,
                    operator: 'IS',
                })),
            });
        });

    const favoriteFilters = [];
    const priorityFilters = [];
    _.forEach(localFilters.discreteFilters?.FEATURES, ({ filter: option, page }) => {
        if (page.val === 'FAVORITES') {
            favoriteFilters.push({ isFavorited: option.value });
        }
        if (page.val === 'PRIORITIES') {
            priorityFilters.push({ priority: option.value });
        }
    });

    if (favoriteFilters.length) {
        filters.push({
            boolean: 'OR',
            filters: favoriteFilters,
        });
    }
    if (priorityFilters.length) {
        filters.push({
            boolean: 'OR',
            filters: priorityFilters,
        });
    }

    const filter = {};

    if (filters.length) {
        filter.filters = filters;
    }

    if (localFilters.freeText) {
        filter.search = [localFilters.freeText];
    }

    if (!_.isEmpty(filter)) {
        allFilters.filters = [filter];
    }

    return allFilters;
}
export function convertApiFiltersToLocal(apiFilters, filterables) {
    const localFilters = {
        id: apiFilters.id,
        sortOrder: {
            value: apiFilters.orderBy[0].field || 'NAME',
            direction: apiFilters.orderBy[0].direction || 'ASC',
        },
        discreteFilters: null,
        currentGroup: null,
        freeText: null,
    };

    if (apiFilters.group) {
        localFilters.currentGroup = apiFilters.group;
    }

    const apiFilterObj = apiFilters.filters?.[0];

    if (apiFilterObj?.search?.length) {
        localFilters.freeText = apiFilterObj.search[0];
    }
    if (apiFilterObj?.filters) {
        const discreteFilters = {
            MARKERS: [],
            FIELDS: [],
            FEATURES: [],
        };

        apiFilterObj.filters.forEach((filter) => {
            if (filter.markers && filter.boolean === 'OR') {
                const markerFilterables = _.find(filterables, { val: 'MARKERS' });
                filter.markers.forEach((markerFilter) => {
                    if (markerFilter.operator !== 'IS') {
                        return;
                    }
                    const markerGroup = _.find(markerFilterables.options, { context: markerFilter.context });
                    const markerOption = _.find(markerGroup.items, { id: markerFilter.markerId });
                    if (markerGroup) {
                        discreteFilters.MARKERS.push({
                            filter: markerOption,
                            filterType: markerFilterables.val,
                            page: markerGroup,
                        });
                    }
                });
            }
            if (filter.fields && filter.boolean === 'OR') {
                const fieldFilterables = _.find(filterables, { val: 'FIELDS' });
                filter.fields.forEach((fieldFilter) => {
                    if (fieldFilter.operator !== 'IS') {
                        return;
                    }
                    const fieldInfo = _.find(fieldFilterables.options, { fieldId: fieldFilter.fieldId });
                    const fieldOption = _.find(fieldInfo.items, { value: fieldFilter.match });
                    if (fieldOption) {
                        discreteFilters.FIELDS.push({
                            filter: fieldOption,
                            filterType: fieldFilterables.val,
                            page: fieldInfo,
                        });
                    }
                });
            }
            if (filter.filters && filter.boolean === 'OR') {
                filter.filters.forEach((featureFilter) => {
                    const featureFilterables = _.find(filterables, { val: 'FEATURES' });
                    if (_.has(featureFilter, 'isFavorited')) {
                        const favoriteInfo = _.find(featureFilterables.options, { val: 'FAVORITES' });
                        const favoriteOption = _.find(favoriteInfo.items, { value: featureFilter.isFavorited });
                        discreteFilters.FEATURES.push({
                            filter: favoriteOption,
                            filterType: featureFilterables.val,
                            page: favoriteInfo,
                        });
                    } else if (_.has(featureFilter, 'priority')) {
                        const priorityInfo = _.find(featureFilterables.options, { val: 'PRIORITIES' });
                        const priorityOption = _.find(priorityInfo.items, { value: featureFilter.priority });
                        discreteFilters.FEATURES.push({
                            filter: priorityOption,
                            filterType: featureFilterables.val,
                            page: priorityInfo,
                        });
                    }
                });
            }
        });

        localFilters.discreteFilters = discreteFilters;
    }

    return localFilters;
}
