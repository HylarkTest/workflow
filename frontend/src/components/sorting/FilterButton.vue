<template>
    <div class="c-filter-button">
        <button
            ref="filterButton"
            type="button"
            class="c-filter-button__box centered relative hover:shadow-primary-600/20 hover:shadow-md"
            :class="bgClass"
            @click="toggleFullFilters"
        >
            <i
                class="far fa-filter-list c-filter-button__filter"
                :class="{ 'text-primary-600': fullFiltersOpen }"
            >
            </i>

            <ClearButton
                v-if="discreteFilters"
                :positioningClass="'-right-1 -top-1 absolute'"
                @click.stop="clearDiscreteFilters"
            >
            </ClearButton>
        </button>

        <FilterPanel
            :isOpen="fullFiltersOpen"
            :modelValue="modelValue"
            v-bind="$attrs"
            @update:modelValue="$emit('update:modelValue', $event)"
            @closeFilters="closeFullFilters"
            @clearFilters="clearFilters"
        >
        </FilterPanel>
    </div>
</template>

<script>

import FilterPanel from './FilterPanel.vue';
import ClearButton from '@/components/buttons/ClearButton.vue';

export default {
    name: 'FilterButton',
    components: {
        ClearButton,
        FilterPanel,
    },
    mixins: [
    ],
    props: {
        bgColor: {
            type: String,
            default: 'gray',
            validator(val) {
                return ['white', 'gray'].includes(val);
            },
        },
        modelValue: {
            type: [Object, null],
            default: null,
        },
    },
    emits: [
        'update:modelValue',
        'clearFilters',
    ],
    data() {
        return {
            fullFiltersOpen: false,
        };
    },
    computed: {
        bgClass() {
            return [
                `c-filter-button__box--${this.bgColor}`,
                { 'c-filter-button__box--active': this.discreteFilters },
            ];
        },
        discreteFilters() {
            return this.modelValue?.discreteFilters;
        },
    },
    methods: {
        closeFullFilters() {
            this.fullFiltersOpen = false;
        },
        toggleFullFilters() {
            this.fullFiltersOpen = !this.fullFiltersOpen;
        },
        clearDiscreteFilters() {
            this.$emit('update:modelValue', {
                ...this.modelValue,
                discreteFilters: null,
            });
        },
        clearFilters() {
            this.$emit('clearFilters');
        },
    },
    created() {

    },
};
</script>

<style scoped>

.c-filter-button {
    &__box {
        height: 36px;
        min-width: 36px;
        width: 36px;

        @apply
            border
            border-transparent
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

        &--active {
            @apply
                bg-primary-100
                border-primary-600
                text-primary-600
            ;
        }
    }
}

</style>
