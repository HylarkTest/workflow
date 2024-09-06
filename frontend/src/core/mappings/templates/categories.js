import { $t } from '@/i18n.js';

import { sectorMap } from '@/core/data/sectors.js';

const other = {
    id: 'other',
};

const industryOptions = () => _.concat(sectorMap(), other);

const industriesCategories = () => industryOptions().map((option) => {
    return {
        name: $t(`sectors.${option.id}`),
    };
});

export const categoriesObj = {
    INDUSTRIES_TEMP: {
        name: $t('labels.industries'), // Industries
        id: 'INDUSTRIES_TEMP',
        items: industriesCategories(),
        templateRefs: ['INDUSTRIES'],
    },
};

export function getCategory(id) {
    return categoriesObj[id];
}
