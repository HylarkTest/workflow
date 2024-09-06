<template>
    <EditFoundation
        class="o-page-edit"
        :tabs="tabs"
        :hideHeader="true"
        :selectedTab="selectedTab"
        :selectedTabHeader="selectedTabHeader"
        :selectedTabDescription="selectedTabDescription"
        tabComponent="IconVertical"
        tabClasses="p-0"
        @selectTab="selectTab"
    >

        <component
            v-if="dataLoaded"
            :is="selectedComponent"
            :page="page"
            :space="space"
            :mapping="fullMapping"
            @closeModal="$emit('closeModal')"
        >
        </component>
    </EditFoundation>
</template>

<script>

import EditFoundation from './EditFoundation.vue';
import PageEditGeneral from './PageEditGeneral.vue';
import PageEditViews from './PageEditViews.vue';
import PageEditDisplay from './PageEditDisplay.vue';
import PageEditHistory from './PageEditHistory.vue';
import PageEditForm from './PageEditForm.vue';
import PageEditLists from './PageEditLists.vue';
import PageEditPersonal from './PageEditPersonal.vue';

import interactsWithSupportWidget from '@/vue-mixins/support/interactsWithSupportWidget.js';

import PAGE from '@/graphql/pages/queries/Page.gql';
import MAPPING from '@/graphql/mappings/queries/Mapping.gql';

import { isActiveBaseCollaborative } from '@/core/repositories/baseRepository.js';
import setsTabSelection from '@/vue-mixins/setsTabSelection.js';
import { createPageFromObject } from '@/core/repositories/pageRepository.js';

const featureTabs = [
    'EVENTS',
    'TODOS',
    'LINKS',
    'NOTES',
    'DOCUMENTS',
    'PINBOARD',
    'CALENDAR',
];

const tabOptions = (pageType, isCollaborativeBase) => [
    {
        icon: 'fal fa-memo',
        value: 'GENERAL',
    },
    {
        icon: 'fal fa-person-rays',
        value: 'PERSONAL',
        types: ['ENTITIES'],
        description: true,
        condition: isCollaborativeBase,
    },
    // {
    //     name: 'Page options',
    //     icon: 'fal fa-sliders-simple',
    //     subtitle: 'Review additional settings for your page',
    //     value: 'OPTIONS',
    // },
    // {
    //     name: 'Filters',
    //     icon: 'fal fa-bars-filter',
    //     subtitle: 'Customize your quick filters',
    //     value: 'FILTERS',
    //     types: ['CALENDAR', 'TODOS'],
    // },
    {
        icon: 'fal fa-pen-field',
        value: 'FORM',
        types: ['ENTITIES'],
        description: true,
    },
    {
        icon: 'fal fa-table-columns',
        value: 'VIEWS',
        types: ['ENTITIES'],
        description: true,
    },
    {
        icon: 'fal fa-browser',
        value: 'DISPLAY',
        types: ['ENTITIES', 'ENTITY'],
        description: true,
    },
    {
        namePath: `customizations.tabs.featureLists.${_.camelCase(pageType)}`,
        icon: 'fal fa-list',
        value: 'LISTS',
        types: ['FEATURES'],
        description: true,
    },
    {
        icon: 'fal fa-history',
        value: 'HISTORY',
    },
];

export default {
    name: 'PageEdit',
    components: {
        EditFoundation,
        PageEditGeneral,
        PageEditViews,
        PageEditDisplay,
        PageEditHistory,
        PageEditForm,
        PageEditLists,
        PageEditPersonal,
    },
    mixins: [
        setsTabSelection,
        interactsWithSupportWidget,
    ],
    props: {
        page: {
            type: Object,
            required: true,
        },
        defaultTab: {
            type: String,
            default: '',
        },
        space: {
            type: [Object, null],
            default: null,
        },
    },
    emits: [
        'closeModal',
        'resetDefaultTab',
    ],
    apollo: {
        fullPage: {
            query: PAGE,
            variables() {
                return {
                    id: this.page.id,
                };
            },
            update: (data) => createPageFromObject(data.page),
            fetchPolicy: 'cache-first',
        },
        mapping: {
            query: MAPPING,
            variables() {
                return { id: this.page.mapping?.id };
            },
            skip() {
                return this.page.type !== 'ENTITIES' && this.page.type !== 'ENTITY';
            },
            fetchPolicy: 'cache-first',
        },
    },
    data() {
        return {
            selectedTab: this.defaultTab || 'GENERAL',
            componentKey: 'PAGE_EDIT',
        };
    },
    computed: {
        fullTab() {
            return _.find(this.tabs, { value: this.selectedTab });
        },
        pageType() {
            return this.page.type;
        },
        isEntities() {
            return ['ENTITIES', 'ENTITY'].includes(this.pageType);
        },
        isFeature() {
            return featureTabs.includes(this.pageType);
        },
        dataLoaded() {
            return this.isEntities ? this.fullMapping : true;
        },
        selectedTabHeader() {
            return this.fullTab.namePath;
        },
        camelTab() {
            return _.camelCase(this.selectedTab);
        },
        selectedTabDescription() {
            // Might be clicked before the mapping has finished loading
            if (this.fullTab.description && this.mapping) {
                return {
                    path: `customizations.tabs.${this.camelTab}.description`,
                    args: { singularName: this.mapping.singularName },
                };
            }
            return '';
        },
        fullMapping() {
            return this.mapping;
        },
        fullTabs() {
            const options = tabOptions(this.pageType, this.isCollaborativeBase);
            return options.map((tab) => {
                const tabValue = _.camelCase(tab.value);
                const namePath = tab.namePath || `customizations.tabs.${_.camelCase(tabValue)}.name`;
                const subtitlePath = `customizations.tabs.${_.camelCase(tabValue)}.subtitle`;
                return {
                    ...tab,
                    namePath,
                    subtitlePath,
                };
            });
        },
        tabs() {
            return this.fullTabs.filter((tab) => {
                if (_.has(tab, 'condition') && !tab.condition) {
                    return false;
                }
                const tabTypes = tab.types;
                if (tabTypes) {
                    const featureCheck = tabTypes.includes('FEATURES');
                    if (featureCheck && this.isFeature) {
                        return featureCheck;
                    }
                    return tab.types.includes(this.pageType);
                }
                return true;
            });
        },
        supportPropsObj() {
            return {
                sectionName: 'Customize a page',
                sectionTitle: 'Pages',
                val: 'CUSTOMIZE_PAGE',
                relevantTopics: ['pages'],
            };
        },
        isCollaborativeBase() {
            return isActiveBaseCollaborative();
        },
    },
    methods: {

    },
    created() {
        this.$emit('resetDefaultTab');
    },
};
</script>

<style scoped>

/*.o-page-edit {

} */

</style>
