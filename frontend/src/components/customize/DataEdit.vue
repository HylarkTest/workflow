<template>
    <div
        v-if="!$apollo.loading"
        class="o-data-edit h-full min-h-0 flex flex-col"
    >
        <LabelHeader
            :labelPath="labelPath"
        >
            {{ currentHeaders.name }}

            <template
                v-if="oppositeView"
                #extra
            >
                <button
                    type="button"
                    class="button button-primary"
                    @click="switchView(currentButton.val)"
                >
                    <i
                        class="far mr-1 fa-fw"
                        :class="currentButton.icon"
                    >
                    </i>
                    {{ currentButton.name }}
                </button>
            </template>

        </LabelHeader>

        <div
            class="text-xs flex justify-end"
            :class="viewHighlightBg"
        >
            <p
                class="py-1 px-4 font-medium"
                :class="viewHighlightText"
            >
                {{ viewHighlightLabel }}
            </p>
            <div
                class="o-data-edit__stripes"
            >

            </div>
        </div>

        <component
            :is="editComponent"
            class="flex-1 min-h-0"
            :page="resolvedPage"
            :mapping="mapping"
            :space="space"
            :defaultTab="functionalDefaultTab"
            v-bind="$attrs"
            @resetDefaultTab="resetDefaultTab"
            @switchSectionAndTab="switchSectionAndTab"
        >
        </component>
    </div>
</template>

<script>

import FeatureEdit from './FeatureEdit.vue';
import MappingEdit from './MappingEdit.vue';
import PageEdit from './PageEdit.vue';
import LabelHeader from '@/components/assets/LabelHeader.vue';

import PAGE from '@/graphql/pages/queries/Page.gql';
import SPACE from '@/graphql/spaces/queries/Space.gql';
import initializeConnections from '@/http/apollo/initializeConnections.js';

import { typeColors } from '@/composables/useDataTypes.js';

const viewObject = {
    PAGE: {
        colorVal: 'PAGE',
        labelKey: 'page',
    },
    MAPPING: {
        colorVal: 'BLUEPRINT',
        labelKey: 'blueprint',
    },
    FEATURE: {
        colorVal: 'FEATURE_PAGE',
        labelKey: 'feature',
    },
};

export default {
    name: 'DataEdit',
    components: {
        FeatureEdit,
        LabelHeader,
        PageEdit,
        MappingEdit,
    },
    mixins: [
    ],
    props: {
        page: {
            type: [Object, null],
            required: true,
        },
        defaultView: {
            type: String,
            default: 'PAGE',
            validator(val) {
                return ['PAGE', 'MAPPING', 'FEATURE'].includes(val);
            },
        },
        defaultTab: {
            type: String,
            default: '',
        },
        blueprint: {
            type: [Object, null],
            default: null,
        },
    },
    apollo: {
        fullPage: {
            query: PAGE,
            skip() {
                return !this.page || this.isMainFeaturePage;
            },
            variables() {
                return { id: this.page.id };
            },
            update: _.property('page'),
            fetchPolicy: 'cache-first',
        },
        space: {
            query: SPACE,
            skip() {
                return !this.page || this.isMainFeaturePage;
            },
            variables() {
                return { id: this.page.space.id };
            },
            update: (data) => initializeConnections(data).space,
            fetchPolicy: 'cache-first',
        },
    },
    data() {
        return {
            view: this.defaultView || 'PAGE',
            functionalDefaultTab: this.defaultTab || '',
        };
    },
    computed: {
        isMainFeaturePage() {
            return this.page?.isMainFeaturePage || false;
        },
        resolvedPage() {
            return this.fullPage || this.page;
        },
        hasPage() {
            return !!this.resolvedPage?.id;
        },
        pageType() {
            return this.resolvedPage?.type;
        },
        isFeatureType() {
            const types = [
                'TODOS',
                'CALENDAR',
                'EVENTS',
                'NOTES',
                'DOCUMENTS',
                'LINKS',
                'PINBOARD',
            ];
            return types.includes(this.pageType);
        },
        isFeaturePage() {
            // Unsure about checking the type name as a condition
            // return this.resolvedPage.__typename === 'ListPage'
            //     || this.isMainFeaturePage;
            return this.isFeatureType || this.isMainFeaturePage;
        },
        featureName() {
            if (this.isFeaturePage) {
                const formattedName = _.camelCase(this.page.type);
                return this.$t(`features.${formattedName}.title`);
            }
            return '';
        },
        mapping() {
            return this.resolvedPage?.mapping || this.blueprint;
        },
        viewCamel() {
            return _.camelCase(this.view);
        },
        viewPascal() {
            return _.pascalCase(this.view);
        },
        possibleSettings() {
            return {
                PAGE: {
                    icon: 'fa-memo',
                    name: this.resolvedPage?.name,
                    val: 'PAGE',
                    displayPath: 'common.page',
                },
                MAPPING: {
                    icon: 'fa-compass-drafting',
                    name: this.mapping?.name,
                    val: 'MAPPING',
                    displayPath: 'common.blueprint',
                },
                FEATURE: {
                    icon: 'fa-stars',
                    name: this.featureName,
                    val: 'FEATURE',
                    displayPath: 'labels.feature',
                },
            };
        },
        currentButton() {
            return this.possibleSettings[this.oppositeView];
        },
        oppositeView() {
            if (!this.hasPage) {
                return null;
            }
            const returnToPage = this.view === 'MAPPING' || this.view === 'FEATURE';
            if (returnToPage) {
                return 'PAGE';
            }
            return this.isFeaturePage ? 'FEATURE' : 'MAPPING';
        },
        currentHeaders() {
            return this.possibleSettings[this.view];
        },
        labelPath() {
            return this.currentHeaders.displayPath;
        },
        editComponent() {
            return `${this.viewPascal}Edit`;
        },
        viewHighlightObj() {
            return viewObject[this.view];
        },
        viewHighlightColor() {
            return typeColors[this.viewHighlightObj.colorVal];
        },
        viewHighlightBg() {
            return `bg-${this.viewHighlightColor}-200`;
        },
        viewHighlightText() {
            return `text-${this.viewHighlightColor}-700`;
        },
        viewHighlightLabel() {
            return this.$t(`labels.${this.viewHighlightObj.labelKey}Customization`);
        },
    },
    methods: {
        switchView(view) {
            this.view = view;
        },
        resetDefaultTab() {
            this.functionalDefaultTab = '';
        },
        switchSectionAndTab(view, tab) {
            this.view = view;
            this.functionalDefaultTab = tab;
        },
    },
    created() {
    },
};
</script>

<style scoped>

.o-data-edit {
    &__stripes {
        background: repeating-linear-gradient(
            45deg,                  /* Angle of the stripes */
            rgba(255, 255, 255, 0), /* Translucent first stripe (50% opacity) */
            rgba(255, 255, 255, 0) 10px, /* Width of the first stripe */
            rgba(255, 255, 255, 0.6) 10px, /* Start of the second stripe with 60% opacity */
            rgba(255, 255, 255, 0.6) 20px  /* Width of the second stripe */
        );
        @apply
            flex-1
        ;
    }
}

</style>
