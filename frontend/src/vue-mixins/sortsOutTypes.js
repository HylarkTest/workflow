import providesColors from '@/vue-mixins/style/providesColors.js';

import {
    isEntity,
} from '@/core/display/typenamesList.js';

export const featureMap = {
    EVENT: {
        featureType: 'EVENTS',
        itemTypename: 'Event',
        listTypename: 'Calendar',
        featurePageType: 'CALENDAR',
        listName: 'calendar',
        symbol: 'fa-calendar-alt',
    },
    TODO: {
        featureType: 'TODOS',
        itemTypename: 'Todo',
        listTypename: 'TodoList',
        featurePageType: 'TODOS',
        listName: 'list',
        symbol: 'fa-square-check',
    },
    LINK: {
        featureType: 'LINKS',
        itemTypename: 'Link',
        listTypename: 'LinkList',
        featurePageType: 'LINKS',
        listName: 'linkList',
        symbol: 'fa-link',
    },
    NOTE: {
        featureType: 'NOTES',
        itemTypename: 'Note',
        listTypename: 'Notebook',
        featurePageType: 'NOTES',
        listName: 'notebook',
        symbol: 'fa-note',
    },
    DOCUMENT: {
        featureType: 'DOCUMENTS',
        itemTypename: 'Document',
        listTypename: 'Drive',
        featurePageType: 'DOCUMENTS',
        listName: 'drive',
        symbol: 'fa-paperclip',
    },
    PIN: {
        featureType: 'PINBOARD',
        itemTypename: 'Pin',
        listTypename: 'Pinboard',
        featurePageType: 'PINBOARD',
        listName: 'pinboard',
        symbol: 'fa-map-pin',
    },
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

export default {
    components: {
    },
    mixins: [
        providesColors,
    ],
    computed: {
        fullObj() {
            // Redefine in component
            return {};
        },
        mapping() {
            return this.fullObj.mapping;
        },
        isEntity() {
            return isEntity(this.fullObj);
        },
        typename() {
            if (this.isEntity) {
                return 'Item';
            }
            return this.fullObj.__typename;
        },

        isFeaturePage() {
            return featurePageTypes.includes(this.typename);
        },
        isFeatureList() {
            return featureListTypes.includes(this.typename);
        },
        isFeatureItem() {
            return featureItemTypes.includes(this.typename);
        },
        isEntityPage() {
            return entityPageTypes.includes(this.typename);
        },

        resultData() {
            if (this.isFeaturePage) {
                return _.find(featureMap, { featurePageType: this.fullObj.type });
            }
            if (this.isFeatureList) {
                return _.find(featureMap, { listTypename: this.typename });
            }
            if (this.isFeatureItem) {
                return _.find(featureMap, { itemTypename: this.typename });
            }
            if (this.isEntityPage || this.isEntity) {
                return _.find(entityMap, { type: this.typename });
            }
            return null;
        },

        isUserData() {
            return !this.resultData;
        },
        isAssignable() {
            return this.isFeatureItem || this.isEntity;
        },

        //names
        objName() {
            return this.fullObj.name;
        },

        mainTag() {
            let mainTag = {};
            if (this.isFeaturePage) {
                const label = this.$t('labels.featurePage');
                mainTag = {
                    label,
                    title: `${label}: ${this.objName}`,
                };
            }
            if (this.isFeatureList) {
                const label = this.$t(`finder.tags.${_.camelCase(this.resultData.listTypename)}`);
                mainTag = {
                    label,
                    title: `${label}: "${this.objName}"`,
                };
            }
            if (this.isFeatureItem) {
                const label = this.$t(`labels.${_.camelCase(this.typename)}`);
                mainTag = {
                    label,
                    title: `${label}: "${this.objName}"`,
                };
            }
            if (this.isEntityPage) {
                const label = this.$t(`finder.tags.${_.camelCase(this.resultData.type)}`);
                mainTag = {
                    icon: 'fa-memo',
                    label,
                    title: `${this.$t('common.page')}: ${label}`,
                };
            }
            if (this.isEntity) {
                const label = this.fullObj.mapping.name;
                mainTag = {
                    icon: 'fa-compass-drafting',
                    label,
                    title: `${this.$t('common.blueprint')}: ${label}`,
                };
            }
            return {
                ...mainTag,
                colorClasses: this.mainTagColorClasses,
            };
        },
        tags() {
            if (this.hasPages) {
                return this.pages.map((page) => {
                    return {
                        icon: 'fa-memo',
                        label: page.name,
                        title: `${this.$t('common.page')}: ${page.name}`,
                        colorClasses: this.tagColorClasses,
                    };
                });
            }
            return [{
                icon: 'fa-memo',
                label: 'No pages found',
                title: 'No pages',
                colorClasses: this.noPagesColorClasses,
            }];
        },

        color() {
            if (this.isFeaturePage) {
                return 'emerald';
            }
            if (this.isFeatureList) {
                return 'turquoise';
            }
            if (this.isFeatureItem) {
                return 'gold';
            }
            if (this.isEntityPage) {
                return 'rose';
            }
            if (this.isEntity) {
                return 'sky';
            }
            return 'violet';
        },
        noPagesColorClasses() {
            return `${this.getBgColor('gray', '100')}
                ${this.getTextColor('gray', '400')}`;
        },
        tagColorClasses() {
            return `${this.getBgColor(this.color, '200')}
                ${this.textColorClass}`;
        },
        mainTagColorClasses() {
            return `${this.getBgColor(this.color, '100')}
                ${this.textColorClass}`;
        },
        textColorClass() {
            return this.getTextColor(this.color);
        },

        pages() {
            return this.fullObj.pages;
        },
        hasPages() {
            return !!this.pages?.length;
        },
        firstPage() {
            return this.hasPages && this.pages[0];
        },
        isPagelessEntity() {
            return this.isEntity && !this.firstPage;
        },
        icons() {
            if (!this.isEntity) {
                // IconsComposition.vue always expects an array of icons.
                return [{
                    symbol: this.resultData.symbol || this.fullObj.symbol,
                    color: this.color,
                }];
            }
            if (this.hasPages) {
                return this.pages.map((page) => {
                    return {
                        symbol: page.symbol,
                        color: this.color,
                    };
                });
            }
            return [];
        },
    },
};
