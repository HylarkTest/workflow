<template>
    <div class="o-filter-main">
        <div class="mb-8">
            <h4 class="o-filter-main__header">
                <i
                    class="fal fa-sort text-primary-600 mr-1"
                >
                </i>
                {{ $t('common.sort') }}
            </h4>
            <div
                class="o-filter-main__content"
            >
                <SortingDropdown
                    class="w-full"
                    :sortOrder="sortOrder"
                    :sortables="sortables"
                    :hideLabel="true"
                    :bgColor="bgColor"
                    @update:sortOrder="$emit('update:sortOrder', $event)"
                >
                </SortingDropdown>
            </div>
        </div>
        <div class="mb-8">
            <h4 class="o-filter-main__header">
                <i
                    class="fal fa-object-group text-primary-600 mr-1"
                >
                </i>
                {{ $t('common.group') }}
            </h4>
            <div
                class="o-filter-main__content"
            >
                <GroupingSelection
                    class="w-full"
                    :currentGroup="group"
                    :mapping="mapping"
                    :bgColor="bgColor"
                    :hideLabel="true"
                    :featureType="featureType"
                    @update:currentGroup="$emit('update:group', $event)"
                >
                </GroupingSelection>
            </div>
        </div>
        <div class="">
            <h4 class="o-filter-main__header flex justify-between items-center">
                <span>
                    <i
                        class="fal fa-filter text-primary-600 mr-1"
                    >
                    </i>
                    {{ $t('common.filters') }}
                </span>

                <div
                    v-if="discreteFiltersLength"
                    class="flex items-center"
                >
                    <button
                        class="button-rounded--xs button-gray mr-1"
                        type="button"
                        @click="clearFilters"
                    >
                        {{ $t('common.clear') }}
                    </button>
                    <div
                        class="o-filter-main__count centered flex-end"
                    >
                        {{ discreteFiltersLength }}
                    </div>
                </div>
            </h4>

            <div class="o-filter-main__content">
                <p
                    v-if="!hasFilterables"
                    class="mt-4 text-cm-500 text-sm text-center"
                >
                    {{ $t('common.noFiltersAvailable') }}
                </p>
                <FiltersContent
                    v-else
                    :filteredFilterables="filteredFilterables"
                    :discreteFilters="discreteFilters"
                    @selectFilter="selectFilter"
                >
                </FiltersContent>
            </div>
        </div>
    </div>
</template>

<script>

import FiltersContent from './FiltersContent.vue';
import SortingDropdown from './SortingDropdown.vue';
import GroupingSelection from '@/components/assets/GroupingSelection.vue';

import providesFilterProperties from '@/vue-mixins/common/providesDiscreteFilterProperties.js';

export default {
    name: 'FilterMain',
    components: {
        FiltersContent,
        GroupingSelection,
        SortingDropdown,
    },
    mixins: [
        providesFilterProperties,
    ],
    props: {
        group: {
            type: [String, null],
            default: null,
        },
        sortables: {
            type: Array,
            required: true,
        },
        filters: {
            type: [Object, null],
            default: null,
        },
        sortOrder: {
            type: Object,
            required: true,
        },
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
        featureType: {
            type: String,
            default: '',
        },
    },
    emits: [
        'update:sortOrder',
        'update:group',
        'update:filters',
    ],
    data() {
        return {
            filterEmitName: 'update:filters',
            filterEmitBase: 'filters',
        };
    },
    computed: {
        discreteFilters() {
            return this.filters
                || null;
        },
        filteredFilterablesLength() {
            return this.filteredFilterables?.length;
        },
        hasFilterables() {
            return !!this.filteredFilterablesLength;
        },
        filteredFilterables() {
            return this.formattedFilterables.filter((filterable) => {
                return filterable.options.length;
            });
        },
    },
    methods: {
        selectFilter({
            isSelected, filter, filterGroup, option,
        }) {
            if (isSelected) {
                this.removeFilter(filter, filterGroup);
            } else {
                this.applyFilter({ value: filter, group: filterGroup, page: option });
            }
        },
    },
    created() {

    },
};
</script>

<style scoped>

.o-filter-main {
    &__header {
        @apply
            border-b
            border-cm-200
            border-solid
            font-semibold
            mb-2
            px-3
            py-1.5
        ;
    }

    &__content {
        @apply
            px-2
        ;
    }

    &__count {
        min-height: 20px;
        min-width: 20px;

        @apply
            bg-primary-600
            font-bold
            p-px
            rounded-full
            text-cm-00
            text-xssm
        ;
    }
}

</style>
