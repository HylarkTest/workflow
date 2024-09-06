<template>
    <div class="c-filter-dropdown">
        <DropdownPaged
            collapseOnSearch
            dropdownComponent="DropdownBasic"
            :optionsPopupProps="optionsPopupProps"
            :modelValue="formattedDiscreteFilters"
            :groups="formattedFilterables"
            :pageKeys="['items']"
            @select="selectFilter"
        >
            <template
                #selected="{ popupState, selectedEvents }"
            >
                <button
                    ref="dropdownButton"
                    type="button"
                    class="c-filter-dropdown__box centered"
                    :class="bgClass"
                    v-on="selectedEvents"
                >
                    <i
                        class="far fa-filter c-filter-dropdown__filter"
                        :class="{ 'text-primary-600': popupState }"
                    >
                    </i>
                    <button
                        v-if="discreteFiltersArray.length"
                        type="button"
                        class="circle-center c-filter-dropdown__clear"
                        @click.stop="$emit('update:discreteFilters', {})"
                    >
                        <i class="far fa-times"></i>
                    </button>
                </button>

            </template>
            <template
                #popupStart
            >
                <h5 class="c-filter-dropdown__select pb-1 pt-2 px-3">
                    {{ $t('common.filterBy') }}...
                </h5>
            </template>
            <template
                #option="scope"
            >
                <DropdownOptions
                    :size="size"
                    v-bind="scope"
                >
                    <template
                        #option
                    >
                        <div
                            v-if="!scope.isLastSeries"
                            class="relative leading-snug"
                        >
                            <i
                                class="fal mr-0.5"
                                :class="scope.original.icon"
                            >
                            </i>
                            {{ scope.original.name }}

                            <div
                                v-if="isSelected(scope.original, scope.page)"
                                class="c-filter-dropdown__circle"
                            >
                            </div>
                        </div>

                        <FilterDropdownOption
                            v-else
                            :option="scope.original"
                            :isSelected="isSelected(scope.original, scope.page)"
                            :slotName="scope.original.slotName || 'option'"
                        >
                            <template
                                v-for="(_, slot) in $slots"
                                #[slot]="optionScope"
                            >
                                <slot
                                    :name="slot"
                                    v-bind="optionScope"
                                ></slot>
                            </template>
                        </FilterDropdownOption>
                    </template>

                </DropdownOptions>
            </template>
        </DropdownPaged>
    </div>
</template>

<script>

import FilterDropdownOption from './FilterDropdownOption.vue';
import hasDropdownAwareArrowControls from '@/vue-mixins/hasDropdownAwareArrowControls.js';
import DropdownPaged from '@/components/dropdowns/DropdownPaged.vue';
// import ClearButton from '@/components/buttons/ClearButton.vue';
import interactsWithDropdowns from '@/vue-mixins/interactsWithDropdowns.js';
import providesDiscreteFilterProperties from '@/vue-mixins/common/providesDiscreteFilterProperties.js';

import { getIcon } from '@/core/display/typenamesList.js';

const filterDropdownProps = {
    popupStyle: {
        padding: '0',
    },
    maxHeightProp: '12.5rem',
    widthProp: '10rem',
};

export default {
    name: 'FilterDropdown',
    components: {
        DropdownPaged,
        FilterDropdownOption,
        // ClearButton,
    },
    mixins: [
        interactsWithDropdowns,
        hasDropdownAwareArrowControls,
        providesDiscreteFilterProperties,
    ],
    props: {
        discreteFilters: {
            type: [Object, null],
            required: true,
        },
        bgColor: {
            type: String,
            default: 'gray',
            validator(val) {
                return ['white', 'gray'].includes(val);
            },
        },
    },
    emits: [
        'update:discreteFilters',
    ],
    data() {
        return {
        };
    },
    computed: {
        bgClass() {
            return `c-filter-dropdown__box--${this.bgColor}`;
        },
        optionsPopupProps() {
            return {
                ...filterDropdownProps,
                ...this.popupProps,
            };
        },
    },
    methods: {
        closeDropdown() {
            this.dropdownActive = false;
        },
        closeReset() {
            this.closeDropdown();
            this.filterView = false;
        },
        filterTypeName(filterValue) {
            const item = _(this.filterables).find(['val', filterValue]);
            return item.namePath;
        },
        setFilterView(filter) {
            this.filterView = filter;
            this.focusOnButton();
        },
        focusOnButton() {
            this.$refs.dropdownButton.focus();
        },
        getIcon(val) {
            return getIcon(val);
        },
        isSelected(option) {
            return _.some(this.discreteFilters, (filters) => {
                return filters.find(({ filter }) => {
                    return option.items
                        ? _.some(option.items, (item) => _.isEqual(item, filter))
                        : _.isEqual(option, filter);
                });
            });
        },
        selectFilter({ value, group, page }) {
            if (this.isSelected(value, group)) {
                this.removeFilter(value, group);
            } else {
                this.applyFilter({ value, group, page });
            }
        },
    },
    created() {

    },
};
</script>

<style scoped>
.c-filter-dropdown {
    @apply flex;

    &__max {
        max-height: 150px;
    }

    &__box {
        height: 36px;

        @apply
            cursor-pointer
            px-2
            relative
            rounded-full
            text-cm-400
        ;

        &--white {
            @apply
                bg-cm-00
            ;
        }

        &--gray {
            @apply
                bg-cm-100
            ;
        }
    }

    &__filter {
        transition: color 0.2s ease-in-out;
    }

    &__select {
        @apply
            font-semibold
            text-cm-500
            text-xs
            uppercase
        ;
    }

    &__active {
        @apply
            flex
            items-center
            mx-2
            my-1
            relative
        ;
    }

    &__clear {
        @apply
            absolute
            bg-primary-600
            cursor-pointer
            h-20p
            -right-2
            text-13p
            text-cm-00
            -top-2
            w-20p
        ;
    }

    &__circle {
        @apply
            absolute
            bg-primary-600
            border
            border-cm-00
            border-solid
            h-2
            -left-2.5
            rounded-full
            top-1
            w-2
        ;
    }
}
</style>
