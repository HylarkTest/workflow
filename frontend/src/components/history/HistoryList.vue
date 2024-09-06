<template>
    <div
        class="o-history-list"
    >
        <template
            v-if="!isLoadingInitial"
        >
            <div
                class="flex flex-col w-full h-full"
            >
                <div
                    v-if="displayFilters"
                    class="mb-8 p-2 bg-cm-00 sticky rounded-xl z-over"
                    :class="topStickyClass"
                >
                    <FilterLine
                        v-model="filtersObj"
                        :filterables="filterables"
                        :sortables="sortables"
                        :page="null"
                    >
                        <template
                            #actionSlot="{ option }"
                        >
                            <div
                                class="border-2 border-solid h-2.5 mr-2 rounded-full w-2.5"
                                :class="option.borderColor"
                            >
                            </div>
                            {{ option.text }}
                        </template>
                    </FilterLine>
                </div>

                <LoadMore
                    v-if="hasActivities && !isLoadingFiltering"
                    :hasNext="hasMore"
                    @nextPage="showMore"
                >
                    <div class="px-2">
                        <div
                            v-for="(date, index) in dateLabels"
                            :key="index"
                            class="mb-12 last:mb-0"
                        >
                            <div class="o-history-list__date text-primary-600">
                                {{ dateDisplay(date) }}
                            </div>

                            <div
                                :class="{ 'bg-cm-00 rounded-2xl p-4': styleWithBg }"
                            >
                                <div
                                    v-for="(action, actionIndex) in groupedActivity[date]"
                                    :key="action.id"
                                >
                                    <HistoryItem
                                        :action="action"
                                        :isSingleItemHistory="!!isSingleItemHistory"
                                        :isLast="isLastInGroup(groupedActivity[date], actionIndex)"
                                    >
                                    </HistoryItem>
                                </div>
                            </div>
                        </div>
                    </div>
                </LoadMore>
            </div>

            <div
                v-if="showNoContentText || showUpgradeMessage"
                class="o-history-list__box"
            >
                <NoContentText
                    v-if="showNoContentText"
                    :customHeaderPath="noContentHeaderPath"
                    :customIcon="noContentIcon"
                >
                </NoContentText>

                <div
                    v-if="showUpgradeMessage"
                    class="mt-6 bg-cm-100 p-4 rounded-xl"
                >
                    <UpgradeMessage
                        :info="upgradeInfo"
                        elementsBgClass="bg-cm-00"
                    >
                    </UpgradeMessage>
                </div>
            </div>
        </template>

        <LoaderFetch
            v-if="isLoadingFiltering || isLoadingInitial"
            class="py-10"
            :sphereSize="50"
            :isFull="true"
        >
        </LoaderFetch>

    </div>
</template>

<script>

import HistoryItem from './HistoryItem.vue';
import LoadMore from '@/components/data/LoadMore.vue';
import FilterLine from '@/components/sorting/FilterLine.vue';
import UpgradeMessage from '@/components/upgrades/UpgradeMessage.vue';

import interactsWithSortables from '@/vue-mixins/interactsWithSortables.js';
import providesHistoryColors from '@/vue-mixins/providesHistoryColors.js';

import ACTIVITY from '@/graphql/history/queries/History.gql';
import initializeConnections from '@/http/apollo/initializeConnections.js';

const upgradeInfo = {
    title: {
        path: 'upgrade.prompts.history.title',
        args: { number: 30 },
    },
    subtitle: 'upgrade.prompts.history.subtitle',
};

export default {
    name: 'HistoryList',
    components: {
        HistoryItem,
        FilterLine,
        UpgradeMessage,
        LoadMore,
    },
    mixins: [
        interactsWithSortables,
        providesHistoryColors,
    ],
    inheritAttrs: false,
    props: {
        showFilters: Boolean,
        // Item id
        globalId: {
            type: String,
            default: '',
        },
        // Feature page
        pageType: {
            type: String,
            default: '',
        },
        // Get all versus top five with button to get more
        showFullFirstPageInitially: Boolean,
        styleWithBg: Boolean,
        topStickyClass: {
            type: String,
            default: 'nav-spacing--sticky',
        },
    },
    emits: [
        'doneLoading',
    ],
    apollo: {
        history: {
            query: ACTIVITY,
            variables() {
                const types = _.map(this.filtersObj?.discreteFilters?.ACTION, 'filter.id');
                const collapseChildren = !this.globalId && !this.pageType;
                return {
                    forNode: this.globalId || null,
                    subjectType: this.pageType ? [this.pageType] : null,
                    search: this.filtersObj.freeText || null,
                    first: this.showFullFirstPageInitially ? 30 : 5,
                    type: types.length ? types : null,
                    collapseChildren,
                    orderBy: [{
                        field: 'CREATED_AT',
                        direction: this.sortDirection.toUpperCase(),
                    }],
                };
            },
            update: initializeConnections,
            fetchPolicy: 'network-only',
        },
    },
    data() {
        return {
            filtersObj: {
                sortOrder: this.startingSortOrder('DATE'),
            },
            filterables: [
                {
                    namePath: 'labels.action',
                    val: 'ACTION',
                    options: [
                        {
                            text: 'Create',
                            id: 'CREATE',
                            borderColor: this.historyBorderColor('CREATE'),
                            classes: 'items-center',
                            slotName: 'actionSlot',
                        },
                        {
                            text: 'Update',
                            id: 'UPDATE',
                            borderColor: this.historyBorderColor('UPDATE'),
                            classes: 'items-center',
                            slotName: 'actionSlot',
                        },
                        {
                            text: 'Delete',
                            id: 'DELETE',
                            borderColor: this.historyBorderColor('DELETE'),
                            classes: 'items-center',
                            slotName: 'actionSlot',
                        },
                        // {
                        //     text: 'Add',
                        //     id: 'ADD',
                        //     borderColor: this.historyBorderColor('ADD'),
                        // },
                        // {
                        //     text: 'Change',
                        //     id: 'CHANGE',
                        //     borderColor: this.historyBorderColor('CHANGE'),
                        // },
                        // {
                        //     text: 'Remove',
                        //     id: 'REMOVE',
                        //     borderColor: this.historyBorderColor('REMOVE'),
                        // },
                    ],
                },
            ],
            sortables: this.validSortables(['DATE']),
            isLoadingMore: false,
        };
    },
    computed: {
        isLoadingInitial() {
            return this.$apollo.loading && !this.isLoadingMore && !this.history;
        },
        isLoadingFiltering() {
            return this.$apollo.loading && !this.isLoadingMore && this.history;
        },
        isSingleItemHistory() {
            return this.globalId;
        },
        activity() {
            return this.history?.history || [];
        },
        groupedActivity() {
            return _.groupBy(this.activity, ({ createdAt }) => this.$dayjs(createdAt).format('YYYY-MM-DD'));
        },
        dateLabels() {
            const dateKeys = _.keys(this.groupedActivity);
            return _.orderBy(dateKeys, _.identity, this.sortDirection);
        },
        sortDirection() {
            return _.lowerCase(this.filtersObj.sortOrder.direction);
        },
        activityConnection() {
            return this.activity?.__HistoryConnection;
        },
        // The total number of filtered activities the user can see (limited by payment plan)
        activityTotal() {
            return this.activityConnection?.pageInfo.total;
        },
        // The total number of activities generated for the current view
        allActivityTotal() {
            return this.activityConnection?.meta.allHistoryCount;
        },
        // The total number of activities the user can see (limited by payment plan)h
        allowedActivityTotal() {
            return this.activityConnection?.meta.allowedHistoryCount;
        },
        // The total number of activities that match the filter
        allFilteredActivityTotal() {
            return this.activityConnection?.meta.filteredHistoryCount;
        },
        // Are there activities to display?
        hasActivities() {
            return !!this.activityTotal;
        },
        hasNoActivities() {
            return !this.hasActivities;
        },
        // Are there activities that the user cannot see (based on payment plan)
        // and are there no activities that they can see.
        hasOlderHiddenActivities() {
            return this.allFilteredActivityTotal !== this.activityTotal;
        },
        hasOlderActivities() {
            return this.allActivityTotal && !this.activityTotal;
        },
        showNoContentText() {
            return !this.$apollo.loading
                && (!this.hasOlderHiddenActivities
                    && (this.hasNoActivities
                    || this.noFilterMatches));
        },
        showUpgradeMessage() {
            return !this.$apollo.loading && this.hasOlderHiddenActivities;
        },
        noContentHeaderPath() {
            if (this.noFilterMatches) {
                return 'common.noFilterMatches';
            }
            if (this.hasNoActivities) {
                return 'history.noContent';
            }
            return '';
        },
        noContentIcon() {
            if (this.noFilterMatches) {
                return 'fa-filter-slash';
            }
            if (this.hasNoActivities) {
                return 'fa-list-timeline';
            }
            return '';
        },
        noFilterMatches() {
            return !this.allFilteredActivityTotal && !!this.allActivityTotal;
        },
        hasMore() {
            return this.activityConnection.pageInfo.hasNextPage;
        },
        displayFilters() {
            return this.showFilters && (this.hasActivities || this.noFilterMatches);
        },
    },
    methods: {
        dateDisplay(keyDate) {
            return this.$dayjs(keyDate).calendar(null, {
                sameDay: '[Today]',
                lastDay: '[Yesterday]',
                lastWeek: 'll',
                sameElse: 'll',
            });
        },
        isLastInGroup(activities, index) {
            return index === (activities.length - 1);
        },
        async showMore() {
            this.isLoadingMore = true;
            const variables = {
                after: this.activityConnection.pageInfo.endCursor,
            };
            await this.$apollo.queries.history.fetchMore({ variables });
            this.isLoadingMore = false;
        },
    },
    created() {
        this.upgradeInfo = upgradeInfo;
    },
};
</script>

<style scoped>

.o-history-list {
    @apply
        text-sm
    ;

    &__date {
        @apply
            font-semibold
            mb-2
            text-sm
        ;
    }

    &__box {
        @apply
            bg-cm-00
            flex
            flex-col
            items-center
            p-8
            rounded-lg
        ;
    }
}

</style>
