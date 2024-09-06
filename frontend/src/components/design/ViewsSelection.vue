<template>
    <DropdownBox
        :modelValue="currentView?.id"
        class="c-views-selection w-full"
        :groups="filteredFullOptions"
        :inlineLabel="inlineLabel"
        :displayRule="viewsDisplay"
        boxShape="rounded"
        property="id"
        @update:modelValue="updateValue"
    >
        <template
            #selected="{ original }"
        >
            <div
                v-if="original"
                class="flex justify-between w-full"
            >
                <div class="u-ellipsis">
                    {{ getViewName(original) }}
                </div>

                <EditButton
                    v-if="isEditableType(original)"
                    size="sm"
                    @click.stop="editView(original)"
                >
                </EditButton>
            </div>
        </template>

        <template
            #option="{ original }"
        >
            <div
                class="flex justify-between w-full"
            >
                <div class="flex items-baseline min-w-0">
                    <i
                        class="mr-1 fal fa-fw shrink-0"
                        :class="getSymbol(original)"
                    >
                    </i>
                    <p class="u-hyphen min-w-0">
                        {{ getViewName(original) }}
                    </p>
                </div>

                <EditButton
                    v-if="isEditableType(original)"
                    size="sm"
                    @click.stop="editView(original)"
                >
                </EditButton>
            </div>
        </template>

        <template
            #popupEnd
        >
            <div class="flex justify-center py-0.5">
                <button
                    class="button--sm bg-cm-100 hover:bg-cm-200"
                    type="button"
                    @click="createView"
                >
                    Create a view
                </button>
            </div>
        </template>

        <template
            #general
        >
            <ViewEditModal
                v-if="isModalOpen && selectedView"
                :view="selectedView"
                :mapping="mapping"
                :page="page"
                @closeModal="closeView"
                @viewCreated="editView"
            >
            </ViewEditModal>
        </template>
    </DropdownBox>
</template>

<script>

import ViewEditModal from '@/components/customize/ViewEditModal.vue';
import EditButton from '@/components/buttons/EditButton.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

import {
    getBirdseyeList,
    getDashboardList,
    getDashboardObj,
} from '@/core/display/fullViews.js';

export default {
    name: 'ViewsSelection',
    components: {
        ViewEditModal,
        EditButton,
    },
    mixins: [
        interactsWithModal,
    ],
    props: {
        currentView: {
            type: [Object, null],
            required: true,
        },
        showViewEdits: Boolean,
        pageType: {
            type: String,
            required: true,
        },
        page: {
            type: [Object, null],
            default: null,
        },
        mapping: {
            type: [Object, null],
            default: null,
        },
    },
    emits: [
        'update:currentView',
    ],
    data() {
        return {
            inlineLabel: {
                text: this.$t('common.views.view'),
                textClass: 'uppercase text-xs text-cm-400 font-semibold',
                position: 'inside',
            },
            selectedViewId: null,
        };
    },
    computed: {
        selectedView() {
            if (this.selectedViewId === 'NEW') {
                return {};
            }
            const foundView = this.allOptions.find((view) => {
                return view.id === this.selectedViewId;
            });
            return foundView || null;
        },
        pageDashboardViews() {
            return this.page?.design?.views || [];
        },
        defaultDashboardViews() {
            return getDashboardList(this.pageType);
        },
        // withoutEditedDefaults() {
        //     return _(this.defaultDashboardViews).filter((view) => {
        //         return !_.find(this.pageDashboardViews, { id: view.id });
        //     }).value();
        // },
        // filteredDashboardViews() {
        //     return _.concat(this.withoutEditedDefaults, this.pageDashboardViews);
        // },
        combinedDashboardViews() {
            return _.map(this.dashboardObj.DASHBOARD);
        },
        fullOptions() {
            return [
                {
                    group: 'Dashboard views',
                    options: this.combinedDashboardViews,
                },
                {
                    group: 'Birds-eye views',
                    options: _.map(getBirdseyeList(this.pageType, this.mappedFeatures)),
                },
            ];
        },
        filteredFullOptions() {
            return _.filter(this.fullOptions, 'options.length');
        },
        allOptions() {
            return _.flatMap(this.fullOptions, 'options');
        },
        features() {
            return this.mapping?.features;
        },
        mappedFeatures() {
            return _.map(this.features, 'val') || [];
        },
        dashboardObj() {
            return getDashboardObj(this.page);
        },

    },
    methods: {
        updateValue(val) {
            const defaultView = _.find(this.allOptions, ['id', val]) || this.allOptions[0];
            this.$emit('update:currentView', defaultView);
        },
        itemLang(item) {
            if (item.categoryType === 'BIRDSEYE') {
                return `links.${_.camelCase(item.id)}`;
            }
            return `views.dashboard.${_.camelCase(item.id)}`;
        },
        isEditableType(view) {
            const isDashboard = view.categoryType === 'DASHBOARD';
            const dashboardTypes = _.keys(this.defaultDashboardViews);
            const isDashboardType = _.includes(dashboardTypes, view.viewType);
            return isDashboard || isDashboardType;
        },
        getViewName(view) {
            return view.name || this.$t(this.itemLang(view));
        },
        getSymbol(original) {
            return original.symbol || this.defaultDashboardViews[original.viewType].symbol;
        },
        closeView() {
            this.isModalOpen = false;
            this.selectedViewId = null;
        },
        editView(view) {
            this.selectedViewId = view.id;
            this.openModal();
        },
        createView() {
            this.selectedViewId = 'NEW';
            this.openModal();
        },
    },
    watch: {
        page: {
            immediate: true,
            handler() {
                if (this.page && !this.currentView) {
                    const defaultViewId = this.page.design?.defaultView || 'LINE';
                    this.updateValue(defaultViewId);
                } else if (this.currentView) {
                    // Replace the current view with that of the page if it was
                    // changed.
                    this.updateValue(this.currentView.id);
                }
            },
        },
    },
    created() {
        this.viewsDisplay = (item) => {
            return this.getViewName(item);
        };
    },
};
</script>

<style scoped>

.c-views-selection {
    @apply
        w-52
    ;

    &__display--border {
        @apply
            border-cm-400
            border-solid
            border-t
        ;
    }
}

</style>
