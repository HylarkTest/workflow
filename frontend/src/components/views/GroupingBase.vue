<template>
    <div
        class="c-grouping-base"
        :class="typeClass"
    >
        <div
            v-if="hasCollapseOption"
            class="flex justify-end mb-4"
        >
            <ExpandCollapseAllButton
                :allSectionsExpanded="areAllOpen"
                @toggleAllOpenState="toggleAllGroupingsOpenState"
            >
            </ExpandCollapseAllButton>
        </div>
        <div
            v-for="(grouping, index) in groupings"
            :key="index"
            class="mb-10 last:mb-0"
            :class="groupingClass"
        >
            <GroupingHeader
                v-if="showHeader"
                class="c-grouping-base__header shadow-primary-300/50"
                :class="headerClass"
                :groupingType="groupingType"
                :grouping="grouping"
                :hasCollapseOption="hasCollapseOption"
                :isHeaderGroupOpen="isGroupOpen(grouping)"
                :hideCount="hideCount"
                :mapping="mapping"
                @toggleGroupingOpenState="toggleGroupingOpenState($event, grouping)"
            >
            </GroupingHeader>

            <div v-show="isGroupOpen(grouping)">
                <slot
                    v-if="useCase === 'list'"
                    name="listSlot"
                    :grouping="grouping"
                    :isOpen="isGroupOpen(grouping)"
                >
                    <div
                        v-for="source in itemSources(grouping.items)"
                        :key="source.list.id"
                        class="mb-6 last:mb-0"
                    >
                        <div
                            v-if="showFeatureHeader && source.items.length"
                            class="mb-2 flex justify-between "
                        >
                            <span class="font-bold text-2xl">
                                {{ source.list.name }}
                            </span>
                            <span
                                v-if="source.list"
                                class="ml-2 uppercase text-cm-400 font-semibold text-sm"
                            >
                                {{ source.list.space.name }}
                            </span>
                        </div>

                        <slot
                            name="itemsSlot"
                            :source="source"
                            :grouping="grouping"
                        >
                        </slot>
                    </div>
                </slot>

                <div v-if="useCase === 'item'">
                    <!-- v-for on this and slot with item -->
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import ExpandCollapseAllButton from '@/components/buttons/ExpandCollapseAllButton.vue';

import interactsWithGroupingLayouts from '@/vue-mixins/interactsWithGroupingLayouts.js';

import { arrRemove } from '@/core/utils.js';

export default {
    name: 'GroupingBase',
    components: {
        ExpandCollapseAllButton,
    },
    mixins: [
        interactsWithGroupingLayouts,
    ],
    props: {
        displayedList: {
            type: [Object, null],
            default: null,
        },
        showFeatureHeader: Boolean,
        listGroupingKey: {
            type: String,
            default: '',
            validator(val) {
                return [
                    '',
                    'notebook',
                    'list',
                    'drive',
                    'pinboard',
                    'linkList',
                    'calendar',
                ].includes(val);
            },
        },
        hideCount: Boolean,
        mapping: {
            type: [Object, null],
            default: null,
        },
    },
    data() {
        return {
            closedGroupVals: [],
        };
    },
    computed: {
        lowerType() {
            return _.camelCase(this.viewType);
        },
        groupingClass() {
            return `c-grouping-base__grouping--${this.lowerType}`;
        },
        headerClass() {
            return `c-grouping-base__header--${this.lowerType}`;
        },
        typeClass() {
            return `c-grouping-base--${this.lowerType}`;
        },
        hasItems() {
            return this.groupings.some((grouping) => grouping.items.length);
        },
        areAllOpen() {
            return this.closedGroupsLength === 0;
        },
        closedGroupsLength() {
            return this.closedGroupVals.length;
        },
        groupVals() {
            return this.groupings.map((grouping) => grouping.header.val);
        },
        viewHasCollapseOption() {
            const exclusions = ['KANBAN'];
            return !exclusions.includes(this.viewType);
        },
        hasCollapseOption() {
            return this.viewHasCollapseOption
                && this.groupingType
                && this.hasItems;
        },
    },
    methods: {
        itemSources(items) {
            if (!items.length) {
                if (this.displayedList) {
                    return [{
                        list: this.displayedList,
                        items,
                    }];
                }
                return [];
            }
            const listGroupingPath = `${this.listGroupingKey}.id`;
            const groupedItems = _.groupBy(items, listGroupingPath);
            const sources = [];

            _.forEach(groupedItems, (results) => {
                sources.push({
                    list: results[0][this.listGroupingKey],
                    items: results,
                });
            });

            return sources;
        },
        toggleGroupingOpenState(state, grouping) {
            if (state) {
                this.closedGroupVals = arrRemove(this.closedGroupVals, grouping.header.val);
            } else {
                this.closedGroupVals.push(grouping.header.val);
            }
        },
        isGroupOpen(grouping) {
            const isGroupClosed = this.closedGroupVals.includes(grouping.header.val);
            return !isGroupClosed;
        },
        toggleAllGroupingsOpenState() {
            if (this.areAllOpen) {
                this.closedGroupVals = this.groupVals;
            } else {
                this.closedGroupVals = [];
            }
        },
    },
    created() {

    },
};
</script>

<style scoped>

.c-grouping-base {
    &--kanban {
        @apply
            flex
            overflow-x-auto
        ;
    }

    &__grouping--kanban {
        min-width: 280px;
        width: 280px;

        @apply
            mr-4
        ;
    }

    &__header {
        @apply
            bg-cm-100
            border
            border-cm-200
            border-solid
            mb-2
            rounded-lg
            shadow-lg
        ;

        &--kanban {
            @apply
                mb-1
                px-4
                py-2
            ;
        }

        &--spreadsheet,
        &--line,
        &--emails,
        &--tile {
            @apply
                px-4
                py-2
                w-full
            ;
        }
    }
}

</style>
