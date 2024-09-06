<template>
    <div class="o-page-edit-views">

        <SettingsHeaderLine
            class="mb-10"
        >
            <template
                #header
            >
                {{ getText('selectDefault') }}
            </template>

            <div class="w-60">
                <DropdownBox
                    v-model="defaultViewForm.defaultView"
                    class="w-full"
                    :options="allViewsArr"
                    bgColor="gray"
                    :disabled="processingDefaultView"
                    :displayRule="viewsDisplay"
                    :property="(option) => option.id"
                >
                </DropdownBox>
            </div>

        </SettingsHeaderLine>

        <SettingsHeaderLine
            class="mb-10"
        >
            <template
                #header
            >
                {{ getText('defaultFilterHeader') }}
            </template>

            <FiltersPicker
                v-model="defaultFilterForm.defaultFilterId"
                :disabled="processingDefaultFilter"
                property="id"
                domain="PUBLIC"
                bgColor="gray"
                :filterables="filterables"
                :page="page"
                :sortables="sortables"
                :placeholder="getText('defaultFilterPlaceholder')"
                :hasGeneralDefaultInitially="true"
                :mapping="mapping"
            >
            </FiltersPicker>

        </SettingsHeaderLine>

        <SettingsHeaderLine>
            <template
                #header
            >

                {{ getText('editAndCreate') }}
            </template>

            <div class="grid gap-5">
                <div
                    v-for="(list, key) in viewsLists"
                    :key="key"
                    class=""
                >
                    <div class="flex justify-between items-baseline">
                        <h3
                            v-t="listHeader(key)"
                            class="text-sm font-semibold text-cm-400 uppercase mb-2"
                        >
                        </h3>

                        <div class="flex flex-col items-end mb-2">
                            <button
                                v-if="allowCustomViews(key)"
                                :disabled="deactivateAddView(list, key)"
                                class="button--sm button-secondary w-fit"
                                :class="{ unclickable: deactivateAddView(list, key) }"
                                type="button"
                                @click="editView({})"
                            >
                                <i class="fa-solid fa-plus mr-0.5"></i>
                                {{ $t('common.add') }}
                            </button>

                            <p
                                v-if="allowCustomViews(key) && deactivateAddView(list, key)"
                                class="text-end text-sm text-cm-500 mr-1 mt-1"
                            >
                                {{ maxViewsText(key) }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <div
                            v-for="(item, itemKey) in list"
                            :key="itemKey"
                            class="my-1 flex justify-between"
                        >
                            <div class="flex items-baseline">
                                <i
                                    class="fal fa-fw text-primary-500 mr-2"
                                    :class="getSymbol(item.viewType, key)"
                                >
                                </i>

                                <span>
                                    {{ itemLang(item, itemKey) }}
                                </span>

                                <div
                                    v-if="isDefaultView(item)"
                                    v-t="'common.default'"
                                    class="o-page-edit-views__default"
                                >

                                </div>
                            </div>

                            <ActionButtons
                                :hideEdit="item.categoryType === 'BIRDSEYE'"
                                :hideDelete="hideDeleteCondition(item)"
                                @delete="openConfirm(item)"
                                @edit="editView(item)"
                            >
                            </ActionButtons>
                        </div>
                    </div>
                </div>
            </div>
        </SettingsHeaderLine>

        <ViewEditModal
            v-if="isModalOpen && selectedView"
            :view="selectedView"
            :mapping="mapping"
            :page="page"
            @closeModal="closeView"
            @viewCreated="editView"
        >
        </ViewEditModal>

        <ConfirmModal
            v-if="confirmDeleteView"
            icon="fal fa-table-columns"
            :headerTextPath="getTextPath('deleteWarningHeader')"
            @closeModal="closeConfirm"
            @proceedWithAction="deleteView"
            @cancelAction="closeConfirm"
        >
            {{ getText('deleteWarningMessage') }}
        </ConfirmModal>

    </div>
</template>

<script>

import {
    getBirdseyeObj,
    getDashboardObj,
    dashboardViews,
} from '@/core/display/fullViews.js';

import ActionButtons from '@/components/buttons/ActionButtons.vue';
import ViewEditModal from '@/components/customize/ViewEditModal.vue';
import ConfirmModal from '@/components/assets/ConfirmModal.vue';
import FiltersPicker from '@/components/pickers/FiltersPicker.vue';

import providesFilterables from '@/vue-mixins/providesFilterables.js';
import interactsWithSortables from '@/vue-mixins/interactsWithSortables.js';
import interactsWithModal from '@/vue-mixins/interactsWithModal.js';
import {
    deletePageView,
    updateMappingPage,
    updatePageDesign,
} from '@/core/repositories/pageRepository.js';
import { featureIcons } from '@/core/display/featureIcons.js';

const maxViews = {
    DASHBOARD: 10,
};

export default {
    name: 'PageEditViews',
    components: {
        ActionButtons,
        ViewEditModal,
        ConfirmModal,
        FiltersPicker,
    },
    mixins: [
        interactsWithModal,
        providesFilterables,
        interactsWithSortables,
    ],
    props: {
        page: {
            type: Object,
            required: true,
        },
        mapping: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            selectedViewId: null,
            defaultViewForm: this.$apolloForm(() => {
                return {
                    defaultView: this.page.design?.defaultView || 'LINE',
                };
            }),
            confirmDeleteView: null,
            processingDefaultView: false,
            processingDefaultFilter: false,
            defaultFilterForm: this.$apolloForm({
                id: this.page.id,
                defaultFilterId: this.page.defaultFilter?.id,
            }),
        };
    },
    computed: {
        selectedView() {
            if (this.selectedViewId === 'NEW') {
                return {};
            }
            const foundView = this.allViewsArr.find((view) => {
                return view.id === this.selectedViewId;
            });
            return foundView || null;
        },
        featuresFull() {
            return this.mapping.features;
        },
        featuresLength() {
            return this.featuresFull.length;
        },
        featuresTypes() {
            return _.map(this.featuresFull, 'val');
        },
        dashboardObj() {
            return getDashboardObj(this.page);
        },
        birdseyeObj() {
            return getBirdseyeObj(this.page, this.featuresTypes);
        },
        viewsLists() {
            const obj = this.dashboardObj;
            if (this.featuresLength) {
                return {
                    ...obj,
                    ...this.birdseyeObj,
                };
            }
            return obj;
        },
        allViewsArr() {
            const concat = _.concat(this.dashboardObj.DASHBOARD, this.birdseyeObj.BIRDSEYE);
            return _(concat).flatMap((item) => {
                return _(item).map((child) => {
                    return child;
                }).value();
            }).value();
        },
    },
    methods: {
        // Translations
        maxViewsText(listKey) {
            return this.$t(this.getTextPath('maxViews'), { listType: _.capitalize(listKey) });
        },
        getText(textKey) {
            return this.$t(this.getTextPath(textKey));
        },
        getTextPath(textKey) {
            return `customizations.tabs.views.${textKey}`;
        },
        listHeader(val) {
            return `views.${_.camelCase(val)}.name`;
        },
        itemLang(item) {
            if (item.name) {
                return item.name;
            }
            return this.$t(`links.${_.camelCase(item.viewType)}`);
        },

        // "Add view" button
        allowCustomViews(key) {
            return Object.keys(maxViews).includes(key);
        },
        deactivateAddView(list, key) {
            if (this.allowCustomViews(key)) {
                return Object.keys(list).length >= maxViews[key];
            }
            return true;
        },

        getSymbol(viewType, key) {
            if (key === 'BIRDSEYE') {
                return featureIcons[viewType].icon;
            }
            return dashboardViews[viewType]?.symbol;
        },
        deleteView() {
            deletePageView(this.confirmDeleteView.id, this.page);
            this.closeConfirm();
        },
        closeView() {
            this.closeModal();
            this.selectedViewId = null;
        },
        editView(view) {
            this.selectedViewId = view.id || 'NEW';
            this.openModal();
        },
        isDefaultView(item) {
            return item.id === this.defaultViewForm.defaultView;
        },
        hideDeleteCondition(item) {
            if (item.categoryType === 'BIRDSEYE') {
                return true;
            }
            if (this.isDefaultView(item)) {
                return true;
            }
            return this.viewsLists.DASHBOARD.length === 1;
        },
        openConfirm(view) {
            this.confirmDeleteView = view;
        },
        closeConfirm() {
            this.confirmDeleteView = null;
        },
        async saveDefaultView() {
            this.processingDefaultView = true;
            try {
                await updatePageDesign(this.defaultViewForm, this.page);
                this.$saveFeedback();
            } finally {
                this.processingDefaultView = false;
            }
        },
        async saveDefaultFilter() {
            this.processingDefaultFilter = true;
            try {
                await updateMappingPage(this.defaultFilterForm, this.page);
                this.$saveFeedback();
            } finally {
                this.processingDefaultFilter = false;
            }
        },
    },
    watch: {
        allViewsArr(allViews) {
            const selectedView = this.selectedView;
            if (selectedView && _.has(selectedView, 'id')) {
                const foundSelectedView = allViews.find((view) => {
                    return view.id === selectedView.id;
                });
                this.selectedViewId = foundSelectedView.id || null;
            }
        },
        'defaultViewForm.defaultView': function onChange() {
            this.saveDefaultView();
        },
        'defaultFilterForm.defaultFilterId': function onChange(newId) {
            if (newId === this.page.defaultFilter?.id) {
                return;
            }
            this.saveDefaultFilter();
        },
        'page.defaultFilter.id': function onDefaultViewChange(id) {
            this.defaultFilterForm.defaultFilterId = id;
        },
        // When the view edit modal updates the default
        'page.design.defaultView': function onDefaultViewChange(id) {
            this.defaultViewForm.defaultView = id;
        },
    },
    created() {
        this.viewsDisplay = (item) => {
            return this.itemLang(item);
        };
    },
};
</script>

<style scoped>

.o-page-edit-views {
    &__default {
        @apply
            bg-primary-200
            font-semibold
            ml-2
            px-2
            py-0.5
            rounded-md
            text-primary-700
            text-xs
        ;
    }
}

</style>
