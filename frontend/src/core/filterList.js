import Fuse from 'fuse.js';

export default function filter(list, filters, fuseOptions) {
    const activeFilters = filters.activeFilters;
    const freeText = filters.freeText;
    const filterArrays = _.mapValues(activeFilters, (f) => _.map(f, 'filter.id'));
    let newList = list.filter((item) => {
        // eslint-disable-next-line guard-for-in
        for (const key in filterArrays) {
            if (filterArrays[key].length && !filterArrays[key].includes(_.get(item, key))) {
                return false;
            }
        }
        return true;
    });

    if (freeText) {
        const options = fuseOptions || { keys: ['name'], threshold: 0.4 };
        newList = _.map(new Fuse(list, options).search(freeText), 'item');
    }

    const sortOrder = filters.sortOrder;
    if (sortOrder) {
        newList = _.orderBy(newList, sortOrder.value, sortOrder.direction.toLowerCase());
    }

    return newList;
}
