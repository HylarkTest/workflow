<template>
    <div
        v-if="deadlines"
        class="o-timekeeper-deadlines"
    >
        <template v-if="hasDeadlines">
            <h2 class="text-3xl font-semibold mb-2">
                Deadlines
            </h2>

            <div
                class="o-timekeeper-deadlines__actions nav-spacing--sticky"
            >
                <!-- <div
                    class="mr-4"
                >
                    <GroupingSelection
                        v-model:currentGroup="currentGroup"
                        bgColor="gray"
                    >
                    </GroupingSelection>
                </div> -->

                <SortingDropdown
                    v-model:sortOrder="sortOrder"
                    bgColor="gray"
                    :sortables="sortables"
                >
                </SortingDropdown>
            </div>

            <div class="bg-cm-00 p-4 rounded-2xl">
                <GroupingBase
                    :groupingType="currentGroup"
                    :groupings="timekeeperGroupings"
                    :viewType="viewType"
                >
                    <template
                        #listSlot="{ grouping }"
                    >
                        <!-- <div
                            v-for="source in itemSources(grouping.items)"
                            :key="source.list.id"
                        >
                        <div
                            v-if="grouping.items.length"
                            class="font-bold text-2xl"
                        >
                            {{ grouping.list.name }}
                        </div>
                         -->

                        <!-- <LoadMore
                            :hasNext="hasMore"
                            @nextPage="showMore"
                        > -->

                        <DeadlineItem
                            v-for="deadline in grouping.items"
                            :key="deadline.id"
                            class="mb-2"
                            :deadline="deadline"
                        >

                        </DeadlineItem>
                    </template>
                </GroupingBase>
            </div>
        </template>

        <NoContentText
            v-else-if="!isLoading"
            class="mt-10"
            customHeaderPath="timekeeper.noContent.header"
            customMessagePath="timekeeper.noContent.description"
            customIcon="fa-hourglass-clock"
        >
        </NoContentText>
    </div>
</template>

<script>

import DeadlineItem from './DeadlineItem.vue';
import SortingDropdown from '@/components/sorting/SortingDropdown.vue';
// import GroupingSelection from '@/components/assets/GroupingSelection.vue';
import GroupingBase from '@/components/views/GroupingBase.vue';
// import LoadMore from '@/components/data/LoadMore.vue';

import interactsWithSortables from '@/vue-mixins/interactsWithSortables.js';
import ENTITIES_SEARCH from '@/graphql/items/EntitySearch.gql';
import initializeConnections from '@/http/apollo/initializeConnections.js';

export default {
    name: 'TimekeeperDeadlines',
    components: {
        DeadlineItem,
        SortingDropdown,
        // GroupingSelection,
        GroupingBase,
        // LoadMore,
    },
    mixins: [
        interactsWithSortables,
    ],
    props: {
        page: {
            type: [Object, null],
            default: null,
        },
    },
    apollo: {
        deadlines: {
            query: ENTITIES_SEARCH,
            variables() {
                const variables = {
                    due: true,
                    orderBy: [{ field: this.sortOrder.value, direction: this.sortOrder.direction }],
                };
                if (this.page?.mapping.id) {
                    variables.mappingId = this.page.mapping.id;
                }
                return variables;
            },
            update(data) {
                return initializeConnections(data).allItems;
            },
        },
    },
    data() {
        return {
            viewType: 'LINE',
            currentGroup: null,
            sortOrder: this.startingSortOrder('DUE_BY'),
            sortables: this.validSortables([
                'DUE_BY',
                'NAME',
            ]),
        };
    },
    computed: {
        isLoading() {
            return this.$apollo.loading;
        },
        hasDeadlines() {
            return this.deadlines?.length;
        },
        timekeeperGroupings() {
            return [{
                header: { val: null },
                items: this.deadlines,
            }];
        },
    },
    methods: {
    },
    created() {
    },
};
</script>

<style scoped>

.o-timekeeper-deadlines {
    &__actions {
        @apply
            bg-cm-00
            flex
            justify-end
            mb-4
            p-2
            rounded-xl
            sticky
            z-over
        ;
    }
}

</style>
