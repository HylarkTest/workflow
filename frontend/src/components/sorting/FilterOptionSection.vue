<template>
    <div class="o-filter-option-section-section">
        <!-- Option header -->
        <ButtonEl
            class="mb-1 mx-2 flex justify-between items-center"
            @click="toggleSection"
        >
            <div class="font-semibold text-cm-400 text-xssm uppercase py-0.5">
                <i
                    v-if="option.icon"
                    class="fal mr-1"
                    :class="option.icon"
                >
                </i>
                {{ option.name }}
            </div>

            <div class="flex items-center">
                <div
                    v-if="hasSelectedItems"
                    class="rounded-full bg-primary-600 h-2 w-2"
                >
                    <!-- {{ selectedGroupLength(option) }} -->
                </div>

                <div
                    class="o-filter-option-section__angle centered bg-primary-100 text-primary-600"
                >
                    <i
                        class="far"
                        :class="isSectionClosed
                            ? 'fa-angle-down'
                            : 'fa-angle-up'"
                        :title="$t('common.toggleSection')"
                    >
                    </i>
                </div>
            </div>

        </ButtonEl>

        <!-- List of possible filters within this option section -->
        <div v-if="!isSectionClosed">
            <button
                v-for="filterItem in option.items"
                :key="filterItem.val"
                class="o-filter-option-section__option"
                type="button"
                @click="selectFilter(filterItem)"
            >
                <FilterDropdownOption
                    :option="filterItem"
                    :isSelected="isSelectedFilter(filterItem)"
                >
                </FilterDropdownOption>
            </button>
        </div>
    </div>
</template>

<script setup>

import {
    computed,
    ref,
} from 'vue';

import FilterDropdownOption from './FilterDropdownOption.vue';

const props = defineProps({
    option: {
        type: Object,
        required: true,
    },
    filterGroup: {
        type: Object,
        required: true,
    },
    discreteFilters: {
        type: [Object, null],
        default: null,
    },
});

const emit = defineEmits([
    'selectFilter',
]);

const isSectionClosed = ref(false);

function toggleSection() {
    isSectionClosed.value = !isSectionClosed.value;
}

const selectedFiltersForGroupType = computed(() => {
    return props.discreteFilters?.[props.filterGroup.val] || [];
});

const selectedItems = computed(() => {
    // Loop through selected filter objs for this type, return any which are in this option's items array.
    return selectedFiltersForGroupType.value?.filter(({ filter }) => {
        return props.option.items.find((item) => _.isEqual(item, filter));
    });
});

const hasSelectedItems = computed(() => {
    return !!selectedItems.value.length;
});

function isSelectedFilter(filter) {
    if (!selectedFiltersForGroupType.value) {
        return false;
    }

    // Check if filter option param is already in the discreteFilters array
    const existingFilter = selectedFiltersForGroupType.value.find((filterObj) => {
        return _.isEqual(filterObj.filter, filter);
    });

    return !!existingFilter;
}

function selectFilter(selectedFilter) {
    const isSelected = isSelectedFilter(selectedFilter);
    const params = {
        isSelected,
        filter: selectedFilter,
        filterGroup: props.filterGroup,
        option: props.option,
    };
    emit('selectFilter', params);
}

</script>

<style scoped>
.o-filter-option-section {

    &__option {
        @apply
            flex
            hover:bg-cm-100
            items-center
            justify-between
            px-2
            py-px
            rounded-lg
            w-full
        ;
    }

    &__angle {
        height: 16px;
        transition: 0.2s ease-in-out;
        width: 16px;

        @apply
            leading-none
            ml-1
            rounded
            text-sm
        ;

        &:hover {
            @apply
                bg-primary-200
            ;
        }
    }
}
</style>
