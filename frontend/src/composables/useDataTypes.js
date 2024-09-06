import { reactive, computed } from 'vue';
import { useRouter } from 'vue-router';
import { $t } from '@/i18n.js';
import providesColors from '@/vue-mixins/style/providesColors.js';
import {
    isEntity,
    symbols,
} from '@/core/display/typenamesList.js';

export const featureMap = {
    EVENT: {
        featureType: 'EVENTS',
        itemTypename: 'Event',
        listTypename: 'Calendar',
        featurePageType: 'CALENDAR',
        listName: 'calendar',
        symbol: symbols.CALENDAR,
    },
    TODO: {
        featureType: 'TODOS',
        itemTypename: 'Todo',
        listTypename: 'TodoList',
        featurePageType: 'TODOS',
        listName: 'list',
        symbol: symbols.TODOS,
    },
    LINK: {
        featureType: 'LINKS',
        itemTypename: 'Link',
        listTypename: 'LinkList',
        featurePageType: 'LINKS',
        listName: 'linkList',
        symbol: symbols.LINKS,
    },
    NOTE: {
        featureType: 'NOTES',
        itemTypename: 'Note',
        listTypename: 'Notebook',
        featurePageType: 'NOTES',
        listName: 'notebook',
        symbol: symbols.NOTES,
    },
    DOCUMENT: {
        featureType: 'DOCUMENTS',
        itemTypename: 'Document',
        listTypename: 'Drive',
        featurePageType: 'DOCUMENTS',
        listName: 'drive',
        symbol: symbols.DOCUMENTS,
    },
    PIN: {
        featureType: 'PINBOARD',
        itemTypename: 'Pin',
        listTypename: 'Pinboard',
        featurePageType: 'PINBOARD',
        listName: 'pinboard',
        symbol: symbols.PINBOARD,
    },
};

export const typeColors = {
    FEATURE_PAGE: 'emerald',
    FEATURE_LIST: 'turquoise',
    FEATURE_ITEM: 'gold',
    ENTITY_PAGE: 'rose',
    PAGE: 'sky',
    BLUEPRINT: 'gold',
    ENTITY: 'sky',
    OTHER: 'violet',
};

const featureItemTypes = [
    'Event',
    'Todo',
    'Link',
    'Note',
    'Document',
    'Pin',
];

const featureListTypes = [
    'Calendar',
    'TodoList',
    'LinkList',
    'Notebook',
    'Drive',
    'Pinboard',
];

const featurePageTypes = [
    'ListPage',
];

const entityMap = {
    ENTITY: {
        type: 'Item',
        symbol: 'fa-browser',
    },
    ENTITY_PAGE: {
        type: 'EntityPage',
        symbol: 'fa-browser',
    },
    ENTITIES_PAGE: {
        type: 'EntitiesPage',
        symbol: 'fa-table-list',
    },
};

const entityPageTypes = [
    'EntitiesPage',
    'EntityPage',
];

export function useDataTypes(fullObj, pageContext) {
    const state = reactive({
        fullObj,
    });

    const item = computed(() => {
        return state.fullObj;
    });

    const id = computed(() => {
        return item.value.id;
    });

    const mapping = computed(() => {
        return item.value.mapping;
    });

    const isEntityComputed = computed(() => {
        return isEntity(item.value);
    });

    const typename = computed(() => {
        if (isEntityComputed.value) {
            return 'Item';
        }
        return item.value.__typename;
    });

    const isFeaturePage = computed(() => {
        return featurePageTypes.includes(typename.value);
    });

    const isFeatureList = computed(() => {
        return featureListTypes.includes(typename.value);
    });

    const isFeatureItem = computed(() => {
        return featureItemTypes.includes(typename.value);
    });

    const isEntityPage = computed(() => {
        return entityPageTypes.includes(typename.value);
    });

    const resultData = computed(() => {
        if (isFeaturePage.value) {
            return _.find(featureMap, { featurePageType: item.value.type });
        }
        if (isFeatureList.value) {
            return _.find(featureMap, { listTypename: typename.value });
        }
        if (isFeatureItem.value) {
            return _.find(featureMap, { itemTypename: typename.value });
        }
        if (isEntityPage.value || isEntityComputed.value) {
            return _.find(entityMap, { type: typename.value });
        }
        return null;
    });

    const isUserData = computed(() => {
        return !resultData.value;
    });

    const isAssignable = computed(() => {
        return isFeatureItem.value || isEntityComputed.value;
    });

    const objName = computed(() => {
        return item.value.name;
    });

    const color = computed(() => {
        if (isFeaturePage.value) {
            return typeColors.FEATURE_PAGE;
        }
        if (isFeatureList.value) {
            return typeColors.FEATURE_LIST;
        }
        if (isFeatureItem.value) {
            return typeColors.FEATURE_ITEM;
        }
        if (isEntityPage.value) {
            return typeColors.ENTITY_PAGE;
        }
        if (isEntityComputed.value) {
            return typeColors.ENTITY;
        }
        return typeColors.OTHER;
    });

    const noPagesColorClasses = computed(() => {
        return `${providesColors.methods.getBgColor('gray', '100')}
                ${providesColors.methods.getTextColor('gray', '400')}`;
    });

    const textColorClass = computed(() => {
        return providesColors.methods.getTextColor(color.value);
    });

    const tagColorClasses = computed(() => {
        return `${providesColors.methods.getBgColor(color.value, '200')}
                ${textColorClass.value}`;
    });

    const mainTagColorClasses = computed(() => {
        return `${providesColors.methods.getBgColor(color.value, '100')}
                ${textColorClass.value}`;
    });

    const mainTag = computed(() => {
        let mainTagObj = {};
        if (isFeaturePage.value) {
            const label = $t('labels.featurePage');
            mainTagObj = {
                label,
                title: `${label}: ${objName.value}`,
            };
        }
        if (isFeatureList.value) {
            const label = $t(`finder.tags.${_.camelCase(resultData.value.listTypename)}`);
            mainTagObj = {
                label,
                title: `${label}: "${objName.value}"`,
            };
        }
        if (isFeatureItem.value) {
            const label = $t(`labels.${_.camelCase(typename.value)}`);
            mainTagObj = {
                label,
                title: `${label}: "${objName.value}"`,
            };
        }
        if (isEntityPage.value) {
            const label = $t(`finder.tags.${_.camelCase(resultData.value.type)}`);
            mainTagObj = {
                icon: 'fa-memo',
                label,
                title: `${$t('common.page')}: ${label}`,
            };
        }
        if (isEntityComputed.value) {
            const label = item.value.mapping.name;
            mainTagObj = {
                icon: 'fa-compass-drafting',
                label,
                title: `${$t('common.blueprint')}: ${label}`,
            };
        }
        return {
            ...mainTagObj,
            colorClasses: mainTagColorClasses.value,
        };
    });

    const pages = computed(() => {
        return item.value.pages;
    });

    const hasPages = computed(() => {
        return !!pages.value?.length;
    });

    const tags = computed(() => {
        if (hasPages.value) {
            return pages.value.map((page) => {
                return {
                    icon: 'fa-memo',
                    label: page.name,
                    title: `${$t('common.page')}: ${page.name}`,
                    colorClasses: tagColorClasses.value,
                };
            });
        }
        return [{
            icon: 'fa-memo',
            label: 'No pages found',
            title: 'No pages',
            colorClasses: noPagesColorClasses.value,
        }];
    });

    const firstPage = computed(() => {
        return hasPages.value && pages.value[0];
    });

    const isPagelessEntity = computed(() => {
        return isEntityComputed.value && !firstPage.value;
    });

    const icons = computed(() => {
        if (!isEntityComputed.value) {
            // IconsComposition.vue always expects an array of icons.
            return [{
                symbol: resultData.value.symbol || item.value.symbol,
                color: color.value,
            }];
        }
        if (hasPages.value) {
            return pages.value.map((page) => {
                return {
                    symbol: page.symbol,
                    color: color.value,
                };
            });
        }
        return [];
    });

    const _router = useRouter();

    function goToDataLocation() {
        if (isFeatureItem.value || isFeatureList.value) {
            const listId = isFeatureList.value
                ? id.value
                : item.value[resultData.value.listName]?.id;

            if (pageContext?.value) {
                _router.push({
                    name: 'feature',
                    params: {
                        pageId: pageContext.value.id,
                        listId,
                    },
                });
            } else {
                _router.push({
                    name: _.camelCase(resultData.value.featurePageType),
                    params: { listId },
                });
            }
        } else if (isEntityPage.value) {
            _router.push({
                name: 'page',
                params: { pageId: id.value },
            });
        } else if (isPagelessEntity.value) {
            _router.push({
                name: 'recordPage',
                params: { itemId: id.value },
            });
        } else if (isEntityComputed.value || isUserData.value) {
            _router.push({
                name: 'entityPage',
                params: { itemId: id.value, pageId: firstPage.value.id },
            });
        } else if (isFeaturePage.value) {
            _router.push({
                name: 'feature',
                params: { pageId: id.value },
            });
        }
    }

    const firstImageUrl = computed(() => {
        return _.get(item.value, 'images[0].value.url');
    });

    const imageObjUrl = computed(() => {
        const image = item.value.image;
        return image?.url || '';
    });

    const showPageIcon = computed(() => {
        // IconsComposition.vue is used for any result that is not an entity.
        // Entity displays prioritize (1) showing an image,
        // then (2) their first page icon if no image exists, then (3) their initials if the entity is pageless.
        // Entities displaying their first page icon use IconsComposition.vue
        return !isEntityComputed.value || (!firstImageUrl.value && hasPages.value);
    });

    const space = computed(() => {
        if (isEntityComputed.value) {
            return mapping.value?.space;
        }
        if (isFeatureItem.value) {
            return item.value[resultData.value.listName].space;
        }
        return item.value.space;
    });

    const spaceName = computed(() => {
        return space.value?.name;
    });

    return {
        mapping,
        isEntityComputed,
        typename,
        isFeaturePage,
        isFeatureList,
        isFeatureItem,
        isEntityPage,
        resultData,
        isUserData,
        isAssignable,
        objName,
        mainTag,
        tags,
        color,
        noPagesColorClasses,
        tagColorClasses,
        mainTagColorClasses,
        textColorClass,
        pages,
        hasPages,
        firstPage,
        isPagelessEntity,
        icons,
        goToDataLocation,
        firstImageUrl,
        space,
        spaceName,
        showPageIcon,
        imageObjUrl,
    };
}
