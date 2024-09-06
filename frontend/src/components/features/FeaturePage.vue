<template>
    <LayoutPage
        class="c-feature-page"
        v-bind="$attrs"
        :headerProps="headerProps"
    >
        <template
            v-if="showTop"
            #top
        >
            <div
                class="flex justify-end"
            >
                <ViewsSelection
                    v-if="hasView"
                    v-model:currentView="pageView"
                    :pageType="featureType"
                >
                </ViewsSelection>

                <RoundedIcon
                    v-if="historyPageType"
                    class="ml-2"
                    icon="fa-list-timeline"
                    title="History"
                    @click="openHistory"
                >
                </RoundedIcon>

                <RoundedIcon
                    class="ml-2"
                    icon="fa-sliders-simple"
                    title="Page customizations"
                    @click="openPageSettings('')"
                >
                </RoundedIcon>
            </div>
        </template>

        <slot>
        </slot>

        <HistoryModal
            v-if="isModalOpen"
            :pageType="historyPageType"
            @closeModal="closeModal"
        >
        </HistoryModal>

        <FeaturesSettingsModal
            v-if="isPageSettingsOpen"
            :page="pageData"
            :defaultTab="defaultSettingsTab"
            @closePageSettings="closePageSettings"
        >
        </FeaturesSettingsModal>
    </LayoutPage>
</template>

<script>

import ViewsSelection from '@/components/design/ViewsSelection.vue';
import LayoutPage from '@/components/layout/LayoutPage.vue';

import interactsWithFeatureSettings from '@/vue-mixins/features/interactsWithFeatureSettings.js';
import interactsWithPageHistory from '@/vue-mixins/interactsWithPageHistory.js';

import { featureIcons } from '@/core/display/featureIcons.js';

export default {
    name: 'FeaturePage',
    components: {
        ViewsSelection,
        LayoutPage,
    },
    mixins: [
        interactsWithFeatureSettings,
        interactsWithPageHistory,
    ],
    props: {
        featureType: {
            type: String,
            required: true,
        },
        page: {
            type: Object,
            default: null,
        },
        isSubsetPage: Boolean,
        historyPageType: {
            type: String,
            required: true,
        },
        subsetHeaderProps: {
            type: [Object, null],
            default: null,
        },
        currentView: {
            type: [Object, null],
            default: null,
        },
        customHeaderPath: {
            type: String,
            default: '',
        },
    },
    emits: [
        'update:currentView',
    ],
    data() {
        return {

        };
    },
    computed: {
        showView() {
            // Until can test kanban
            return false;
        },
        hasView() {
            return this.currentView && this.showView;
        },
        showTop() {
            return this.historyPageType || this.isSubsetPage || this.hasView;
        },
        header() {
            return this.customHeaderPath || `features.${this.featureTypeFormatted}.title`;
        },
        mainHeaderProps() {
            return {
                name: this.$t(this.header),
                iconProp: `fa-regular ${featureIcons[this.featureType].icon}`,
            };
        },
        headerProps() {
            return this.subsetHeaderProps || this.mainHeaderProps;
        },
        featureTypeFormatted() {
            return _.camelCase(this.featureType);
        },
        pageView: {
            get() {
                return this.currentView;
            },
            set(val) {
                this.$emit('update:currentView', val);
            },
        },
        pageData() {
            return this.page || { isMainFeaturePage: true, type: this.featureType };
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.c-feature-page {

} */

</style>
