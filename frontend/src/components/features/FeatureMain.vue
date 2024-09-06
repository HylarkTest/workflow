<template>
    <div
        class="o-feature-main flex h-full items-start min-h-full"
    >
        <LayoutSide
            v-if="!isSideMinimized"
            :isResponsiveDisplay="isResponsiveDisplay"
            :hasOverlay="true"
            :layoutSideClass="layoutSideClass"
            @minimizeSide="minimizeSide"
        >
            <FeatureSide
                v-model:filtersObj="filtersObj"
                :sources="sources"
                :getFilterCount="getFilterCount"
                :featureStats="featureStats"
                :pendingDelete="pendingDelete"
                :featureType="featureType"
                :sortables="sortables"
                :displayedList="dynamicDisplayedList"
                :showPageSettings="isSubsetPage"
                :currentView="currentView"
                :canMoveItemToList="!!moveItemToListFunction"
                :hideAllLineOptions="hideAllLineOptions"
                :availableSources="pageSources"
                :freePlaceholder="freePlaceholder"
                :page="pageObj"
                :showTotal="showTotal"
                :contextSideFilters="contextSideFilters"
                @saveList="saveList"
                @deleteList="tryDelete"
                @selectList="goToList"
                @moveList="moveList"
                @moveItem="moveItemToList"
                @addNewList="addNewList"
                @removePending="removePendingList"
                @editPageSettings="openPageSettings('LISTS')"
            >
            </FeatureSide>
        </LayoutSide>

        <div
            v-if="existingListsLength"
            class="flex-1 bg-cm-00 rounded-2xl min-w-0"
        >
            <slot
                :displayedList="dynamicDisplayedList"
                :filtersObj="filtersObj"
                :isSideMinimized="isSideMinimized"
                :events="{ minimize: minimizeSide }"
            >
                <FeatureContent
                    v-if="displayedList || hasActiveFilters"
                    v-model:filtersObj="filtersObj"
                    :page="pageObj"
                    :isSideMinimized="isSideMinimized"
                    :displayedList="dynamicDisplayedList"
                    :node="node"
                    :sortables="sortables"
                    :featureType="featureType"
                    :topHeaderClass="topHeaderClass"
                    :hasReducedPadding="hasReducedPadding"
                    :defaultAssociations="defaultAssociations"
                    :enableFileDrop="enableFileDrop"
                    :currentView="currentView"
                    :forceNoDrag="forceNoDrag"
                    :spaceId="displayedListSpaceId"
                    @saveList="saveList"
                    @minimizeSide="minimizeSide"
                >
                    <template
                        #newButton
                    >
                        <slot
                            name="newButton"
                        >
                        </slot>
                    </template>
                </FeatureContent>
            </slot>
        </div>

        <div
            v-else
            class="centered w-full"
        >
            <NoContentText
                :customHeaderPath="noListsHeaderPath"
                :customMessagePath="noListsMessagePath"
            >
                <template
                    #graphic
                >
                    <BirdImage
                        class="h-20"
                        whichBird="FlyingUpBird_72dpi.png"
                    >
                    </BirdImage>
                </template>
            </NoContentText>
        </div>

        <ConfirmModal
            v-if="confirmModal"
            :headerTextPath="confirmHeaderTextPath"
            @closeModal="dontDelete"
            @cancelAction="dontDelete"
            @proceedWithAction="deleteList(pendingDelete)"
        >
            <p v-if="canPendingBeDeleted">
                {{ $t('warnings.integrations.description') }}
            </p>

            <p v-else>
                {{ $t('warnings.integrations.leaveShared') }}
            </p>

            <p
                v-if="isPendingSharedAndOwner"
                class="mt-3"
            >
                {{ $t('warnings.integrations.listMembers') }}
            </p>
        </ConfirmModal>

        <FeaturesSettingsModal
            v-if="isPageSettingsOpen"
            :page="page"
            :defaultTab="defaultSettingsTab"
            @closePageSettings="closePageSettings"
        >
        </FeaturesSettingsModal>
    </div>
</template>

<script>

import FeatureSide from '@/components/features/FeatureSide.vue';
import FeatureContent from '@/components/features/FeatureContent.vue';
import LayoutSide from '@/components/layout/LayoutSide.vue';
import ConfirmModal from '@/components/assets/ConfirmModal.vue';

import interactsWithFeatureSettings from '@/vue-mixins/features/interactsWithFeatureSettings.js';
import listensToScrollandResizeEvents from '@/vue-mixins/listensToScrollAndResizeEvents.js';
import providesFilterProperties from '@/vue-mixins/providesFilterProperties.js';
import interactsWithRouterTitles from '@/vue-mixins/interactsWithRouterTitles.js';
import interactsWithSortables from '@/vue-mixins/interactsWithSortables.js';

import { addListToPage } from '@/core/repositories/pageRepository.js';

import {
    featureFiltersObj,
    getFeatureFilters,
} from '@/core/display/featureFilters.js';

const sortablesMap = {
    DOCUMENTS: {
        sortables: [
            'CREATED_AT',
            'UPDATED_AT',
            'NAME',
            'SIZE',
            'EXTENSION',
            'FAVORITES',
        ],
    },
    EVENTS: {
        sortables: [
            'CREATED_AT',
            'UPDATED_AT',
            'DATE',
            'NAME',
        ],
        sortablesCondition() {
            return this.currentView.viewType === 'CALENDAR';
        },
    },
    TODOS: {
        startingSortOrder: 'MANUAL',
        sortables: [
            'MANUAL',
            'NAME',
            'DUE_BY',
            'PRIORITY',
            'CREATED_AT',
            'UPDATED_AT',
        ],
    },
};

export default {
    name: 'FeatureMain',
    components: {
        FeatureSide,
        FeatureContent,
        LayoutSide,
        ConfirmModal,
    },
    mixins: [
        listensToScrollandResizeEvents,
        providesFilterProperties,
        interactsWithFeatureSettings,
        interactsWithRouterTitles,
        interactsWithSortables,
    ],
    props: {
        featureType: {
            type: String,
            required: true,
            validator(val) {
                return [
                    'LINKS',
                    'TODOS',
                    'EVENTS',
                    'EMAILS',
                    'PINBOARD',
                    'DOCUMENTS',
                    'NOTES',
                ].includes(val);
            },
        },
        featureStats: {
            type: [Object, null],
            default: null,
        },
        page: {
            type: [Object, null],
            default: null,
        },
        isSubsetPage: Boolean,
        forceNoDrag: Boolean,
        deleteListFunction: {
            type: [Function, null],
            required: true,
        },
        createListFromObjectFunction: {
            type: [Function, null],
            required: true,
        },
        updateListFunction: {
            type: [Function, null],
            required: true,
        },
        createListFunction: {
            type: [Function, null],
            required: true,
        },
        moveListFunction: {
            type: [Function, null],
            required: true,
        },
        moveItemToListFunction: {
            type: [Function, null],
            default: null,
        },
        lists: {
            type: Array,
            required: true,
        },
        sourceLists: {
            type: [Array, null],
            required: true,
        },
        integrations: {
            type: [Array, null],
            default: null,
        },
        integrationLists: {
            type: [Object, null],
            default: null,
        },
        renewals: {
            type: [Object, null],
            default: null,
        },
        forceResponsiveDisplay: Boolean,
        defaultFilter: {
            type: [String, null],
            default: null,
        },
        node: {
            type: [Object, null],
            default: null,
        },
        defaultAssociations: {
            type: [Array, null],
            default: null,
        },
        hasReducedPadding: Boolean,
        topHeaderClass: {
            type: String,
            default: 'stickies--body',
        },
        enableFileDrop: Boolean,
        currentView: {
            type: [Object, null],
            default: null,
        },
        responsiveBreakpoint: {
            type: Number,
            default: 768,
        },
        layoutSideClass: {
            type: String,
            default: '',
        },
        allowRouterTitle: Boolean,
        hideAllLineOptions: Boolean,
        isBirdseyePage: Boolean,
        freePlaceholder: {
            type: [String, null],
            default: 'Search by name',
        },
        showTotal: Boolean,
        contextSideFilters: {
            type: [Array, null],
            default: null,
        },
    },
    emits: [
        'setDisplayedList',
    ],
    data() {
        return {
            routeName: this.$route.name,
            isSideMinimized: true,
            displayedList: null,
            isResponsiveDisplay: this.forceResponsiveDisplay,
            pendingDelete: null,
            confirmModal: false,
            previousList: null,
            pendingLists: {},
            filtersObj: {
                currentGroup: null,
                sortOrder: this.getStartingSortOrder(),
            },
        };
    },
    computed: {
        // Filters
        sortableObject() {
            return sortablesMap[this.featureType];
        },
        sortables() {
            if (this.hasExternalFunctionality) {
                return [];
            }
            const obj = this.sortableObject;
            const defaultSortables = [
                'CREATED_AT',
                'UPDATED_AT',
                'NAME',
                'FAVORITES',
            ];

            let sortables = defaultSortables;

            if (obj) {
                const hasCondition = _.has(obj, 'sortablesCondition');
                if (hasCondition && obj.sortablesCondition.call(this)) {
                    return null;
                }
                if (obj.sortables) {
                    sortables = obj.sortables;
                }
            }
            return this.validSortables(sortables);
        },

        // Lists
        hasExternalFunctionality() {
            return this.displayedList?.isExternalList() && !this.hasActiveFilters;
        },
        availableLists() {
            return _.flatMap(this.allSources, 'lists');
        },
        existingLists() {
            return this.availableLists.filter((list) => !list.new);
        },
        existingListsLength() {
            return this.existingLists.length;
        },
        hasNoExistingLists() {
            return !this.existingListsLength;
        },
        dynamicDisplayedList() {
            const foundList = this.availableLists.find((list) => list.id === this.displayedList?.id);
            // Backup in case something happens, but foundList should be there.
            return foundList || this.displayedList;
        },
        displayedListSpaceId() {
            return this.displayedList?.space?.id;
        },
        pendingSpacesIds() {
            return _(this.pendingLists).keys().uniq().value();
        },
        showNoListsMessage() {
            return this.hasNoLists;
        },
        combinedSourceSpaces() {
            // So that it shows on the side, incorporate pending ids
            const spaceIds = this.sourceLists.map((space) => space.id);

            let pendingIds = [];
            if (_.isArray(this.pendingSpacesIds)) {
                pendingIds = this.pendingSpacesIds;
            }

            const notIncludedInMain = _.difference(pendingIds, spaceIds);

            let pendingSpaces = [];
            if (notIncludedInMain.length) {
                pendingSpaces = _(notIncludedInMain).map((id) => {
                    return this.pageSources.spaces.find((space) => space.id === id);
                }).compact().value();
            }
            return this.sourceLists.concat(pendingSpaces);
        },
        sources() {
            const sourceSpaces = this.combinedSourceSpaces.map((source) => {
                return {
                    ...source,
                    lists: [
                        ...(source.lists || []),
                        ...(this.pendingLists[source.id] || []),
                    ],
                };
            });
            return {
                spaces: sourceSpaces,
                integrations: (this.integrations || []).map((integration) => {
                    return {
                        name: integration.accountName,
                        id: integration.id,
                        provider: integration.provider,
                        renewalUrl: this.renewals[integration.id] || null,
                        lists: [
                            ...(this.integrationLists[integration.id]?.data || []),
                            ...(this.pendingLists[integration.id] || []),
                        ],
                    };
                }),
            };
        },
        pageSources() {
            return {
                spaces: this.page?.space ? [this.page.space] : [],
                // For later when integrated lists can be added to feature subset pages
                integrations: [],
            };
        },
        allSources() {
            const spaces = this.sources.spaces || [];
            const integrations = this.integrationsSource || [];
            return spaces.concat(integrations);
        },
        sourcesLength() {
            return this.allSources.length;
        },
        hasIntegrations() {
            return !!this.integrationsSource.length;
        },
        integrationsSource() {
            return this.sources.integrations;
        },
        inContextFeatures() {
            // If item (aka node) or birdseye view
            return this.node || this.isBirdseyePage;
        },
        shouldUseRouter() {
            return !this.inContextFeatures;
        },
        pageType() {
            return this.page?.type;
        },

        // Deletion
        isOwnerOfPendingDelete() {
            return this.pendingDelete?.isOwner;
        },
        isPendingDeleteShared() {
            return this.pendingDelete?.isShared;
        },
        canPendingBeDeleted() {
            return !this.isPendingDeleteShared || this.isOwnerOfPendingDelete;
        },
        isPendingSharedAndOwner() {
            return this.isPendingDeleteShared && this.isOwnerOfPendingDelete;
        },
        confirmHeaderTextPath() {
            return this.canPendingBeDeleted
                ? 'warnings.integrations.header'
                : 'warnings.integrations.leaveListHeader';
        },
        camelFeature() {
            return _.camelCase(this.featureType);
        },
        routerTitle() {
            const listType = this.$t(`links.${this.camelFeature}`);
            if (this.mainFilter) {
                const filterLabel = this.$t(`labels.basicFilters.${this.mainFilter}`);
                return `${listType} - ${filterLabel}`;
            }
            if (this.displayedList) {
                return `${listType} - ${this.displayedList.name}`;
            }
            return null;
        },
        noListsPrefix() {
            return `features.${this.camelFeature}.noLists`;
        },
        noListsHeaderPath() {
            return `${this.noListsPrefix}.header`;
        },
        noListsMessagePath() {
            return `${this.noListsPrefix}.description`;
        },
        pageObj() {
            return this.page || this.systemPage;
        },
        systemPage() {
            return {
                val: this.featureType,
            };
        },
        hasBasicFilters() {
            return !!this.basicFilters.length;
        },
        basicFilters() {
            if (this.contextSideFilters) {
                return getFeatureFilters(this.contextSideFilters);
            }
            const featureVal = featureFiltersObj[this.featureType];
            if (_.isArray(featureVal)) {
                return featureVal || [];
            }
            if (_.isObject(featureVal) && this.currentView) {
                return featureVal[this.viewType] || [];
            }
            return [];
        },
    },
    methods: {
        // Side and resize
        minimizeSide(state) {
            if (_.isUndefined(state)) {
                this.isSideMinimized = !this.isSideMinimized;
            } else {
                this.isSideMinimized = state;
            }
        },
        onResize() {
            const el = this.$el;
            const elWidth = el.offsetWidth;
            if (elWidth < this.responsiveBreakpoint) {
                if (!this.isResponsiveDisplay) {
                    this.isResponsiveDisplay = true;
                    this.isSideMinimized = true;
                }
            } else if (!this.forceResponsiveDisplay) {
                this.isResponsiveDisplay = false;
                this.isSideMinimized = false;
            }
        },

        // List things
        goToList({ list }) {
            if (this.isResponsiveDisplay && !this.isSideMinimized) {
                this.minimizeSide(true);
            }

            if (!list.new) {
                const goToPreviousList = this.previousList?.id === list.id;
                if (!this.shouldUseRouter || goToPreviousList) {
                    this.selectList({ list });
                } else {
                    // Update the url
                    const params = list.route().params;
                    this.$router.push({ name: this.routeName, params });
                }
            }
        },
        goToFirstList() {
            let source = this.allSources[0];

            if (source) {
                if (this.displayedList) {
                    const displayedSource = this.displayedList.space || this.displayedList.account;
                    source = this.findSource(displayedSource);
                }

                const list = source.lists[0];

                if (list) {
                    this.selectList({ list });
                    if (this.shouldUseRouter) {
                        const params = list.route().params;
                        this.$router.replace({ name: this.routeName, params });
                    }
                }
            }
        },

        selectList({ list }) {
            this.displayedList = list;
        },

        setDisplayedList() {
            if ((this.$route.name !== this.routeName) && this.shouldUseRouter) {
                // If the user changes page before everything has loaded we
                // don't want to override the link.
                return;
            }
            let providerId;
            let listId;

            if (this.shouldUseRouter) {
                providerId = this.$route.params.providerId;
                listId = this.$route.params.listId;
            }

            if (!listId) {
                listId = providerId || this.displayedList?.id;
                providerId = null;
            }

            // If there is no list in the URL then we redirect them to the first
            // list in their project.
            if (!listId) {
                if (!(
                    this.displayedList
                    && _(this.allSources).flatMap('lists').map('id').value()
                        .includes(this.displayedList.id)
                )) {
                    this.goToFirstList();
                }
            } else {
                // Then we look through all their sources and integrations to find
                // the list and select it.
                for (const source of this.allSources) {
                    for (const list of source.lists) {
                        if (list.id === listId && (!providerId || source.id === providerId)) {
                            this.selectList({ list });
                            return;
                        }
                    }
                }

                // If we still can't find the list then we redirect them to their
                // first list.
                if (this.shouldUseRouter) {
                    this.$router.replace({ name: this.routeName });
                }
                this.goToFirstList();
            }
        },
        addNewList({ newList, source }) {
            // const usableSource = source || this.page?.source;
            // if (!this.pendingLists[usableSource.id]) {
            //     this.pendingLists[usableSource.id] = [];
            // }
            // this.pendingLists[usableSource.id].push(this.createListFromObjectFunction(newList));
            if (!this.pendingLists[source.id]) {
                this.pendingLists[source.id] = [];
            }
            this.pendingLists[source.id].push(this.createListFromObjectFunction(newList));
        },
        async saveList({ form, list, source }) {
            if (!form.id) {
                const reference = this.findPendingList(list, source);
                reference.name = form.name;

                const response = await this.createListFunction(form, source);

                this.removePendingList(list, source);

                if (this.page?.lists) {
                    await addListToPage(this.page, response);
                }
                this.goToList({ list: this.createListFromObjectFunction(response) });
            } else {
                this.updateListFunction(form, list);
            }
        },
        findSource(source) {
            return _.find(this.allSources, { id: source.id });
        },
        findPendingList(list, source) {
            return _.find(this.pendingLists[source.id], { id: list.id });
        },
        removePendingList(list, source) {
            _.remove(this.pendingLists[source.id], { id: list.id });
        },
        moveList({ list, from, to }) {
            let previousList;
            if (to === 0) {
                previousList = null;
            } else {
                const index = to < from ? to - 1 : to;
                const lists = _.find(this.lists, ({ space }) => space.id === list.space.id).lists;
                previousList = lists[index];
            }
            return this.moveListFunction(list, previousList);
        },

        // Filters and sorting
        getStartingSortOrder() {
            // No access to computed
            const obj = this.sortableObject;
            const order = obj?.startingSortOrder || 'CREATED_AT';
            return this.startingSortOrder(order);
        },
        deselectListForFilters() {
            if (this.displayedList) {
                this.previousList = this.displayedList;
                this.displayedList = null;
            }
        },
        resetListAfterFilters() {
            const resetList = this.previousList;
            if (resetList && !this.displayedList) {
                this.selectList({ list: resetList });
            } else {
                this.setDisplayedList();
            }
            this.resetActiveFilters();
        },
        resetActiveFilters() {
            this.filtersObj.discreteFilters = null;
            this.filtersObj.freeText = '';
            this.filtersObj.filter = null;
        },
        getFilterCount(filter) {
            if (!this.featureStats) {
                return 0;
            }
            if (filter.id === 'all') {
                return this.featureStats.totalCount;
            }
            return this.featureStats[`${filter.id}Count`];
        },
        setFilters(val, key) {
            this.filtersObj[key] = val;
        },

        // Move item to list
        moveItemToList(event) {
            if (this.moveItemToListFunction) {
                this.moveItemToListFunction(event.item, event.list);
            }
        },

        // Deletion
        tryDelete({ list }) {
            this.pendingDelete = list;
            if (list.account?.provider) {
                this.openConfirm();
            } else {
                this.deleteList(list);
            }
        },
        openConfirm() {
            this.confirmModal = true;
        },
        dontDelete() {
            this.closeConfirm();
            this.clearPendingDelete();
        },
        clearPendingDelete() {
            this.pendingDelete = null;
        },
        closeConfirm() {
            this.confirmModal = false;
        },
        async deleteList(list) {
            this.closeConfirm();
            try {
                await this.deleteListFunction(list);

                const sameList = this.displayedList?.id === list.id;
                const refreshList = sameList || this.mainFilter;

                if (refreshList) {
                    this.displayedList = null;
                    this.setDisplayedList();
                }
            } finally {
                this.clearPendingDelete();
            }
        },
    },
    watch: {
        lists: {
            immediate: true,
            handler(val, oldVal) {
                // Set displayed list on load (no oldVal) or if displayedList removed via different tab/window
                const flattenedNewSpacesLists = val.flatMap((space) => space.lists);
                const flattenedOldSpacesLists = oldVal && oldVal.flatMap((space) => space.lists);

                const listsOnlyInOldArray = _.differenceBy(flattenedOldSpacesLists, flattenedNewSpacesLists, 'id');
                const displayedListRemoved = listsOnlyInOldArray.some((list) => list.id === this.displayedList?.id);

                if (!oldVal || displayedListRemoved) {
                    this.setDisplayedList();
                }
            },
        },
        // We could do a deep watch here, but it is unnecessary. The
        // integrationLists prop is an object of arrays. We care if each array
        // changes in order to update the displayed list with whatever the new
        // list is. So here we loop through the integrations and set a shallow
        // watcher for each array of lists.
        // The function starts by unwatching all previously set watchers for
        // good measure.
        integrationLists: {
            immediate: true,
            handler(val, oldVal) {
                if (this.integrationListWatchers) {
                    this.integrationListsWatchers.forEach((unwatch) => unwatch());
                }
                this.integrationListWatchers = [];
                if (val && !oldVal) {
                    _.forEach(val, (lists, key) => {
                        this.integrationListWatchers.push(this.$watch(
                            `integrationLists.${key}`,
                            (val2, oldVal2) => {
                                if (!oldVal2) {
                                    this.setDisplayedList();
                                }
                            }
                        ));
                    });
                    this.setDisplayedList();
                }
            },
        },
        hasContentFilters(newVal, oldVal) {
            if (newVal) {
                this.deselectListForFilters();

                if (!this.mainFilter && this.hasBasicFilters) {
                    this.setFilters('all', 'filter');
                }
            } else if (oldVal && !this.mainFilter) {
                this.resetListAfterFilters();
            }
        },
        mainFilter(newVal, oldVal) {
            if (newVal && !this.hasContentFilters) {
                this.deselectListForFilters();
            } else if (!newVal && oldVal) {
                this.resetListAfterFilters();
            }
        },
        displayedList(newVal, oldVal) {
            // In the interim, for attachments where the parent needs to know
            // the list, sorry!
            this.$emit('setDisplayedList', newVal);
            if (newVal && !oldVal) {
                this.resetActiveFilters();
            }
        },
    },
    created() {
        this.$emit('setDisplayedList', this.displayedList);

        if (this.shouldUseRouter) {
            this.$watch(() => `${this.$route.params.listId}|${this.$route.params.providerId}`, () => {
                if (this.$route.name === this.routeName) {
                    this.setFilters(null, 'filter');
                    this.setDisplayedList();
                }
            });
        }
        if (this.defaultFilter) {
            this.filtersObj.filter = this.defaultFilter;
        }
    },
    mounted() {
        if (!this.forceResponsiveDisplay) {
            this.$nextTick(() => {
                this.onResize();
            });
        }
    },
};
</script>

<style scoped>

/*.o-feature-main {

} */

</style>
