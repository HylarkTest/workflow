<template>
    <div class="o-entities-content">
        <div class="p-4 bg-cm-00 sticky rounded-xl stickies--body mb-8 z-cover">
            <FilterLine
                ref="filterLine"
                v-model="filtersObj"
                :filterables="filterables"
                :sortables="sortables"
                :page="page"
                :mapping="mapping"
                showGrouping
                :hasNewFilterButton="true"
                :hasSavedFilters="true"
                @clearFilters="clearFilters"
            >
            </FilterLine>
            <div
                class="absolute flex justify-center -bottom-4 w-full"
            >
                <button
                    class="button button-rounded button-secondary"
                    type="button"
                    @click="addNew({})"
                >
                    <i class="fa-solid fa-plus mr-1">
                    </i>

                    {{ $t('common.add') }}

                </button>
            </div>
        </div>

        <LoaderFetch
            v-if="queriesLoading"
            class="flex-1 mt-20"
            :isFull="true"
        >
        </LoaderFetch>

        <GroupingBase
            v-if="!isLoadingItems && groups"
            class="px-3"
            :groupingType="groupOfCurrentQuery"
            :groupings="groups"
            :viewType="currentView.viewType"
            :mapping="mapping"
        >
            <template
                #listSlot="{ grouping }"
            >
                <LoadMore
                    v-if="grouping.items?.length"
                    :hasNext="hasMore(grouping)"
                    @nextPage="showMore(grouping)"
                >
                    <component
                        :is="viewLayout"
                        :items="grouping.items"
                        :page="page"
                        :mapping="mapping"
                        :isDraggable="true"
                        :currentView="currentView"
                    >
                    </component>
                </LoadMore>
            </template>
        </GroupingBase>

        <template
            v-if="!isLoadingItems"
        >
            <NoContentText
                v-if="!hasItems && !noMatchingItems"
                class="mt-4"
                customHeaderPath="records.noContent.header"
                customMessagePath="records.noContent.description"
                :customIcon="page.symbol"
            >
            </NoContentText>

            <NoContentText
                v-if="noMatchingItems"
                class="mt-4"
                customHeaderPath="common.noFilterMatches"
            >
                <template
                    #graphic
                >
                    <BirdImage
                        class="h-20"
                        whichBird="MagnifyingGlassBird_72dpi.png"
                    >
                    </BirdImage>
                </template>
            </NoContentText>
        </template>

        <Modal
            v-if="isModalOpen"
            modalKey="addEntityModal"
            containerClass="p-4 w-600p"
            @closeModal="closeModal"
        >
            <EntityNew
                :mapping="mapping"
                :page="page"
                :includeAddAnother="true"
                @closeModal="closeModal"
                @saved="refresh"
            >
            </EntityNew>
        </Modal>

    </div>
</template>

<script>

import { gql } from '@apollo/client';

import {
    buildGetManyRequest,
    buildGroupedRequest,
    buildItemFragment,
} from '@/http/apollo/buildMappingRequests.js';

import FilterLine from '@/components/sorting/FilterLine.vue';
import IconButton from '@/components/buttons/IconButton.vue';

import GroupingBase from '@/components/views/GroupingBase.vue';
import LineLayout from '@/components/views/LineLayout.vue';
import TileLayout from '@/components/views/TileLayout.vue';
import SpreadsheetLayout from '@/components/views/SpreadsheetLayout.vue';
import KanbanLayout from '@/components/views/KanbanLayout.vue';
import LoadMore from '@/components/data/LoadMore.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';
import interactsWithSortables from '@/vue-mixins/interactsWithSortables.js';
import interactsWithEventBus from '@/vue-mixins/interactsWithEventBus.js';
import providesFilterables from '@/vue-mixins/providesFilterables.js';

import {
    initializeItems,
    ITEM_ASSOCIATED,
    ITEM_DISASSOCIATED,
    ITEM_UPDATED,
    RELATIONSHIP_ADDED,
    RELATIONSHIP_REMOVED,
} from '@/core/repositories/itemRepository.js';

import { TODO_CREATED, TODO_DELETED, TODO_UPDATED } from '@/core/repositories/todoRepository.js';
import { PIN_CREATED, PIN_DELETED } from '@/core/repositories/pinRepository.js';
import { NOTE_CREATED, NOTE_DELETED } from '@/core/repositories/noteRepository.js';
import { LINK_CREATED, LINK_DELETED } from '@/core/repositories/linkRepository.js';
import { EVENT_CREATED, EVENT_DELETED } from '@/core/repositories/eventRepository.js';
import { DOCUMENT_CREATED, DOCUMENT_DELETED } from '@/core/repositories/documentRepository.js';
import handlesListAndGroupedItems from '@/vue-mixins/features/handlesListAndGroupedItems.js';

import {
    createDynamicSmartQuery,
    createDynamicSmartSubscription,
} from '@/core/helpers/apolloHelpers.js';
import { convertLocalFiltersToApiFilters } from '@/core/helpers/filterConverter.js';
import interactsWithApolloQueries from '@/vue-mixins/interactsWithApolloQueries.js';

const featureEvents = [
    TODO_CREATED,
    TODO_DELETED,
    TODO_UPDATED,
    EVENT_CREATED,
    EVENT_DELETED,
    PIN_CREATED,
    PIN_DELETED,
    NOTE_CREATED,
    NOTE_DELETED,
    LINK_CREATED,
    LINK_DELETED,
    DOCUMENT_CREATED,
    DOCUMENT_DELETED,
];

export default {
    name: 'EntitiesContent',
    components: {
        FilterLine,
        IconButton,

        GroupingBase,
        LineLayout,
        SpreadsheetLayout,
        TileLayout,
        KanbanLayout,
        LoadMore,
    },
    mixins: [
        interactsWithModal,
        interactsWithSortables,
        interactsWithEventBus,
        handlesListAndGroupedItems,
        providesFilterables,
        interactsWithApolloQueries,
    ],
    props: {
        page: {
            type: Object,
            required: true,
        },
        currentView: {
            type: [Object, null],
            default: null,
        },
        mapping: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'refresh',
    ],
    data() {
        const startingFilters = {
            sortOrder: this.startingSortOrder('NAME'),
            currentGroup: null,
        };
        return {
            // The group headers need to stay there until the next query has
            // finished loading. So this variable will update to the same as
            // currentGroup when the query is done.
            groupOfCurrentQuery: null,
            filtersObj: startingFilters,
            startingFilters,
            listeners: {
                refetchOnItemUpdate: [ITEM_UPDATED],
                refetchOnFeatureChange: featureEvents,
                refetchOnRelationshipChange: [RELATIONSHIP_ADDED, RELATIONSHIP_REMOVED],
                refetchOnAssociationChange: [ITEM_ASSOCIATED, ITEM_DISASSOCIATED],
            },
        };
    },
    computed: {
        isLoadingItems() {
            return this.isWaitingForDefaultFilters
                || this.queriesLoading;
        },
        queriesLoading() {
            const queryParam = this.isGrouping
                ? ['groupedItems']
                : ['items'];
            return this.$isLoadingQueriesFirstTimeOrFromChange(queryParam);
        },
        isGrouping() {
            return this.filtersObj.currentGroup;
        },
        noMatchingItems() {
            if (this.isGrouping) {
                const connections = this.groupedItems?.groups.map((items) => {
                    return this.getConnection(items).pageInfo;
                });
                return _.every(connections, (pageInfo) => {
                    return pageInfo.total === 0 && pageInfo.rawTotal > 0;
                });
            }
            const connection = this.getConnection(this.items)?.pageInfo;
            return connection.total === 0 && connection.rawTotal > 0;
        },

        hasItems() {
            if (this.isGrouping) {
                return _.some(this.groupedItems?.groups, (group) => group.length > 0);
            }
            return this.items?.length;
        },

        viewLayout() {
            // LineLayout
            // SpreadsheetLayout
            // TileLayout
            // KanbanLayout
            return `${_.camelCase(this.currentView.viewType)}Layout`;
        },

        // Groupings
        groups() {
            return this.getGroupings(this.items, this.groupedItems);
        },
        isSpreadsheetView() {
            return this.currentView.viewType === 'SPREADSHEET';
        },
        relationIds() {
            const visibleData = this.currentView.visibleData;
            if (visibleData) {
                return _(visibleData).filter({ dataType: 'RELATIONSHIPS' }).map('formattedId').value();
            }
            // Unlike featureIds, relationIds need to be specified to fetch all
            // of them. But like featureIds, only the spreadsheet view should
            // fetch all relationships when none are specified in `visibleData`.
            return this.isSpreadsheetView ? _.map(this.mapping.relationships, 'id') : [];
        },
        featureIds() {
            const visibleData = this.currentView.visibleData;
            if (visibleData) {
                return visibleData
                    .filter((data) => {
                        return data.dataType === 'FEATURES'
                            && !_.endsWith(data.formattedId, '_FEATURE_NEW')
                            && !_.endsWith(data.formattedId, '_FEATURE_GO');
                    })
                    .map((data) => data.formattedId);
            }
            // If featureIds is null then it will fetch all, if it is an empty
            // array then it will fetch none. Only the spreadsheet view should
            // fetch all features when none are specified in `visibleData`.
            return this.isSpreadsheetView ? null : [];
        },
        queryDependsOnFields() {
            const variables = this.generateVariables();
            return _.some(variables.orderBy, (order) => order.field.startsWith('field:'))
                || this.filtersByFields(variables.filter || []);
        },
        isWaitingForDefaultFilters() {
            return this.page?.activeDefaultFilter && this.$isLoadingQueries(['markerGroups']);
        },
        groupPointer() {
            return this.filtersObj.currentGroup;
        },
    },
    methods: {
        filtersByFields(filters) {
            return _.some(filters, (filter) => {
                if (filter.fields?.length) {
                    return true;
                }
                if (filter.filters?.length) {
                    return this.filtersByFields(filter.filters);
                }
                return false;
            });
        },

        addNew() {
            this.isModalOpen = true;
        },
        refresh() {
            this.$emit('refresh');
        },
        refetch() {
            if (this.isGrouping) {
                this.$apollo.queries.groupedItems.refetch();
            } else {
                this.$apollo.queries.items.refetch();
            }
        },
        refetchOnFeatureChange(feature) {
            if (feature.associations) {
                this.refetch();
            }
        },
        refetchOnRelationshipChange(relationship) {
            if (this.relationIds.includes(relationship.id)) {
                this.refetch();
            }
        },
        refetchOnAssociationChange(item) {
            if (_.some(this.items, ['id', item.id])) {
                this.refetch();
            }
        },
        refetchOnItemUpdate() {
            if (this.isGrouping && _.startsWith(this.filtersObj.currentGroup, 'field:')) {
                this.$apollo.queries.groupedItems.refetch();
            } else if (this.queryDependsOnFields) {
                this.$apollo.queries.items.refetch();
            }
        },
        generateVariables() {
            const variables = convertLocalFiltersToApiFilters(this.filtersObj);

            if (this.page.markerFilters) {
                variables.markers = this.page.markerFilters
                    .map((filter) => _.pick(filter, ['markerId', 'operator', 'context']));
            }
            if (this.page.fieldFilters) {
                variables.fields = this.page.fieldFilters
                    .map((filter) => _.pick(filter, ['fieldId', 'operator', 'match']));
            }

            return variables;
        },
        hasMore(grouping) {
            return this.hasMoreToLoad(grouping, this.items, this.groupedItems);
        },
        async showMore(grouping) {
            const query = this.isGrouping
                ? this.$apollo.queries.groupedItems
                : this.$apollo.queries.items;
            const variables = {};
            if (this.isGrouping) {
                variables.includeGroups = [grouping.header.val];
                variables.after = this.getConnection(grouping.items).pageInfo.endCursor;
            } else {
                variables.after = this.getConnection(this.items).pageInfo.endCursor;
            }
            await query.fetchMore({ variables });
        },
        clearFilters() {
            this.filtersObj = this.startingFilters;
        },
        setToFirstGroup() {
            this.$refs.filterLine?.setToFirstGroupingOption();
        },
    },
    watch: {
        'filtersObj.currentGroup': function updateQuery(val) {
            if (!val) {
                this.groupOfCurrentQuery = null;
            }
        },
        'currentView.viewType': {
            immediate: true,
            handler(val) {
                if (val === 'KANBAN' && !this.isGrouping) {
                    this.setToFirstGroup();
                }
            },
        },
        'page.activeDefaultFilter': {
            immediate: true,
            handler(filter) {
                if (filter && !this.isWaitingForDefaultFilters) {
                    this.filtersObj = filter.toLocalFilters(this.filterables);
                }
            },
        },
        filterables: {
            immediate: true,
            handler(filterables) {
                const pageFilter = this.page.activeDefaultFilter;
                if (pageFilter && !this.isWaitingForDefaultFilters) {
                    this.filtersObj = pageFilter.toLocalFilters(filterables);
                }
            },
        },
    },
    created() {
        // These queries/subscriptions cannot be defined in the apollo object
        // because of the way apollo handles dynamic queries.
        // If the query is a function then it will reload the entire query when
        // it detects a change in the query object, even if the query hasn't
        // changed. This causes a problem when the mapping is updated in a
        // separate tab because the subscription triggers the query to reload
        // before the new mapping info is applied, so it makes a request with
        // the wrong mapping data.
        // Here we are manually setting the watchers, so it only reloads the
        // query when the query string is different.
        createDynamicSmartQuery(
            this,
            'items',
            () => {
                return buildGetManyRequest(
                    this.mapping,
                    null,
                    null,
                    this.relationIds,
                    this.featureIds
                );
            },
            {
                variables() {
                    return this.generateVariables();
                },
                skip() {
                    return !this.mapping || this.isGrouping || this.isWaitingForDefaultFilters;
                },
                update({ items }) {
                    return initializeItems(items)[this.mapping.apiName];
                },
            }
        );

        createDynamicSmartQuery(
            this,
            'groupedItems',
            () => {
                return buildGroupedRequest(
                    this.mapping,
                    null,
                    null,
                    this.relationIds,
                    this.featureIds
                );
            },
            {
                variables() {
                    return this.generateVariables();
                },
                skip() {
                    return !this.mapping || !this.isGrouping || this.isWaitingForDefaultFilters;
                },
                update({ groupedItems }) {
                    return initializeItems(groupedItems)[this.mapping.apiName];
                },
                result({ networkStatus }) {
                    if (networkStatus === 7) {
                        this.groupOfCurrentQuery = this.filtersObj.currentGroup;
                    }
                },
            }
        );

        ['Created', 'Deleted', 'Updated'].forEach((event) => {
            const queryCb = event === 'Updated'
                ? () => {
                    const fragment = buildItemFragment(this.mapping, null, null, this.relationIds, this.featureIds);
                    return gql`
                        subscription { items {
                            ${this.mapping.apiName} {
                                ${this.mapping.apiSingularName}Updated {
                                    ${this.mapping.apiSingularName} {
                                        ${fragment}
                                    }
                                }
                            }
                        } }
                    `;
                }
                : () => gql`subscription { items {
                    ${this.mapping.apiName} {
                        ${this.mapping.apiSingularName}${event} { success }
                    }
                } }`;

            createDynamicSmartSubscription(
                this,
                `item${event}`,
                queryCb,
                {
                    result() {
                        this.refetch();
                    },
                }
            );
        });
    },
};
</script>

<style scoped>

/*
.o-entities-content {

}
*/

</style>
