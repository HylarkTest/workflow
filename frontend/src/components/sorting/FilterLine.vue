<template>
    <div class="o-filter-line">
        <div class="o-filter-line__filters">
            <FreeFilter
                v-if="!hideFreeFilter"
                class="o-filter-line__free"
                :modelValue="modelValue.freeText"
                :bgColor="bgColor"
                v-bind="$attrs"
                @update:modelValue="$proxyEvent($event, modelValue, 'freeText')"
            >
            </FreeFilter>

            <FilterButton
                v-if="hasFilterables && hasNewFilterButton"
                :bgColor="bgColor"
                :filterables="filterables"
                :sortables="sortables"
                :modelValue="modelValue"
                :mapping="mapping"
                :page="page"
                :hasSavedFilters="hasSavedFilters"
                @update:modelValue="$emit('update:modelValue', $event)"
                @clearFilters="clearFilters"
            >
            </FilterButton>

            <FilterDropdown
                v-if="hasFilterables && !hasNewFilterButton"
                class="o-filter-line__dropdown"
                :filterables="filterables"
                :discreteFilters="modelValue.discreteFilters || {}"
                :bgColor="bgColor"
                @update:discreteFilters="$proxyEvent($event, modelValue, 'discreteFilters')"
            >
                <template
                    v-for="(_, slot) in $slots"
                    #[slot]="scope"
                >
                    <slot
                        :name="slot"
                        v-bind="scope"
                    ></slot>
                </template>
            </FilterDropdown>
        </div>
        <div class="flex flex-wrap gap-2 justify-end">

            <GroupingSelection
                v-if="showGrouping"
                ref="groupingSelection"
                :mapping="mapping"
                :currentGroup="modelValue.currentGroup"
                :bgColor="bgColor"
                :hideValue="true"
                :hideToggleButton="true"
                :hasNewFilterButton="true"
                :page="page"
                :filterables="filterables"
                :sortables="sortables"
                :filtersObj="filtersObj"
                @update:currentGroup="$proxyEvent($event, modelValue, 'currentGroup')"
            >
            </GroupingSelection>

            <SortingDropdown
                :sortables="sortables"
                :sortOrder="sortOrder"
                :bgColor="bgColor"
                :hideValue="true"
                :hideToggleButton="true"
                :hasNewFilterButton="hasNewFilterButton"
                :mapping="mapping"
                :page="page"
                :filterables="filterables"
                :filtersObj="filtersObj"
                @update:sortOrder="$proxyEvent($event, modelValue, 'sortOrder')"
            >
            </SortingDropdown>
        </div>
    </div>
</template>

<script>
import FreeFilter from './FreeFilter.vue';
import FilterButton from './FilterButton.vue';
import SortingDropdown from './SortingDropdown.vue';
import FilterDropdown from './FilterDropdown.vue';
import GroupingSelection from '@/components/assets/GroupingSelection.vue';

export default {
    name: 'FilterLine',
    components: {
        FreeFilter,
        FilterButton,
        SortingDropdown,
        FilterDropdown,
        GroupingSelection,
    },
    props: {
        modelValue: {
            type: Object,
            default: () => ({}),
        },
        filterables: {
            type: Array,
            required: true,
        },
        sortables: {
            type: Array,
            required: true,
        },
        hideFreeFilter: Boolean,
        showGrouping: Boolean,
        mapping: {
            type: [null, Object],
            default: null,
        },
        bgColor: {
            type: String,
            default: 'gray',
            validator(value) {
                return ['white', 'gray'].includes(value);
            },
        },
        hasNewFilterButton: Boolean,
        page: {
            type: [Object, null],
            required: true,
        },
        hasSavedFilters: Boolean,
    },
    emits: [
        'update:modelValue',
        'clearFilters',
    ],
    data() {
        return {
        };
    },
    computed: {
        sortOrder() {
            return this.modelValue.sortOrder;
        },
        hasFilterables() {
            return this.filterables?.length;
        },
        filtersObj() {
            return {
                sortOrder: this.modelValue.sortOrder,
                discreteFilters: this.modelValue.discreteFilters,
                currentGroup: this.modelValue.currentGroup,
            };
        },
    },
    methods: {
        clearFilters() {
            this.$emit('clearFilters');
        },
        setToFirstGroupingOption() {
            this.$refs.groupingSelection.setToFirstOption();
        },
    },
    created() {

    },
};
</script>

<style scoped>
.o-filter-line {
    @apply
        flex
        flex-col
        items-end
    ;

    &__filters {
        @apply
            flex
            flex-col-reverse
            items-end
            mb-2
        ;
    }

    &__free {
        @apply
            mt-1
        ;
    }

    &__dropdown {
        @apply
            mb-2
        ;
    }

    @media (min-width: 900px) {
        & {
            @apply
                flex
                flex-row
                items-center
                justify-between
            ;
        }

        &__filters {
            @apply
                flex-row
                items-center
                mb-0
            ;
        }

        &__free {
            @apply
                mr-4
            ;
        }

        &__dropdown {
            @apply
                mb-0
                mr-2
            ;
        }
    }
}
</style>
