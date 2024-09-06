<template>
    <EditFoundation
        class="o-mapping-edit"
        :tabs="mappedTabs"
        :hideHeader="true"
        :selectedTab="selectedTab"
        :selectedTabHeader="selectedTabHeader"
        tabComponent="IconVertical"
        tabClasses="p-0 rounded-b-xl"
        @selectTab="selectTab"
    >

        <component
            v-if="fullMapping"
            :is="selectedComponent"
            :mapping="fullMapping"
            :page="page"
            @switchSectionAndTab="switchSectionAndTab"
        >
        </component>
    </EditFoundation>
</template>

<script>

import EditFoundation from './EditFoundation.vue';
import MappingEditGeneral from './MappingEditGeneral.vue';
import MappingEditFeatures from './MappingEditFeatures.vue';
import MappingEditRelationships from './MappingEditRelationships.vue';
import MappingEditHistory from './MappingEditHistory.vue';
import MappingEditMarkers from './MappingEditMarkers.vue';
import MappingEditFields from './MappingEditFields.vue';

import MAPPING from '@/graphql/mappings/queries/Mapping.gql';

import setsTabSelection from '@/vue-mixins/setsTabSelection.js';
import interactsWithSupportWidget from '@/vue-mixins/support/interactsWithSupportWidget.js';

const tabs = [
    {
        icon: 'fal fa-compass-drafting',
        value: 'GENERAL',
    },
    {
        icon: 'fal fa-sitemap',
        value: 'FIELDS',
    },
    {
        icon: 'fal fa-tags',
        value: 'MARKERS',
    },
    {
        icon: 'fal fa-stars',
        value: 'FEATURES',
    },
    {
        icon: 'fal fa-draw-circle',
        value: 'RELATIONSHIPS',
    },
    {
        icon: 'fal fa-history',
        value: 'HISTORY',
    },
];

export default {
    name: 'MappingEdit',
    components: {
        EditFoundation,
        MappingEditGeneral,
        MappingEditRelationships,
        MappingEditFeatures,
        MappingEditHistory,
        MappingEditFields,
        MappingEditMarkers,
    },
    mixins: [
        setsTabSelection,
        interactsWithSupportWidget,
    ],
    props: {
        mapping: {
            type: Object,
            required: true,
        },
        defaultTab: {
            type: String,
            default: '',
        },
        page: {
            type: [Object, null],
            default: null,
        },
    },
    emits: [
        'switchSectionAndTab',
        'resetDefaultTab',
    ],
    apollo: {
        fullMapping: {
            query: MAPPING,
            variables() {
                return {
                    id: this.mapping.id,
                };
            },
            update: (data) => data.mapping,
            fetchPolicy: 'cache-first',
        },
    },
    data() {
        return {
            useDefaultTabIfProvided: true,
            selectedTab: this.defaultTab || tabs[0].value,
            componentKey: 'MAPPING_EDIT',
        };
    },
    computed: {
        selectedTabHeader() {
            return `customizations.tabs.${_.camelCase(this.selectedTab)}.name`;
        },
        supportPropsObj() {
            return {
                sectionName: 'Customize a blueprint',
                sectionTitle: 'Blueprints',
                val: 'CUSTOMIZE_BLUEPRINT',
                contentQuery: 'Blueprints',
                relevantTopics: ['custom-records'],
            };
        },
        mappedTabs() {
            return tabs.map((tab) => {
                const camelCaseVal = _.camelCase(tab.value);
                return {
                    ...tab,
                    name: this.$t(`customizations.tabs.${camelCaseVal}.name`),
                    subtitle: this.$t(`customizations.tabs.${camelCaseVal}.subtitleBlueprint`),
                };
            });
        },
    },
    methods: {
        switchSectionAndTab(tab) {
            this.$emit('switchSectionAndTab', 'PAGE', tab);
        },
    },
    created() {
        this.tabs = tabs;
        this.$emit('resetDefaultTab');
    },
};
</script>

<style scoped>

/*.o-mapping-edit {

} */

</style>
