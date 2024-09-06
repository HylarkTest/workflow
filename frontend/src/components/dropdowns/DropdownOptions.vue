<template>
    <div
        class="c-dropdown-options w-full relative hyphens-auto"
        :class="optionClasses"
    >
        <slot
            name="option"
            :display="display"
            :isSelected="isSelected"
            :original="original"
            v-bind="$attrs"
        >
            {{ display }}
        </slot>

        <div
            v-if="isSelected && hasCircleForSelected"
            class="c-dropdown-options__circle"
        >
        </div>

        <ClearButton
            v-if="hasRemoveIcon && isSelected"
            positioningClass="absolute right-1"
        >
        </ClearButton>
    </div>
</template>

<script>

import ClearButton from '@/components/buttons/ClearButton.vue';

export default {
    name: 'DropdownOptions',
    components: {
        ClearButton,
    },
    mixins: [
    ],
    props: {
        display: {
            // If the parent wants a custom display slot they might want the
            // display as an object
            type: [String, Object],
            required: true,
        },
        position: {
            type: String,
            required: true,
        },
        isSelected: Boolean,
        original: {
            type: [String, Object],
            required: true,
        },
        isHovered: Boolean,
        size: {
            type: String,
            default: 'base',
            validator(value) {
                return ['base', 'sm', 'lg'].includes(value);
            },
        },
        group: {
            type: [String, Object],
            default: '',
        },
        hasRemoveIcon: Boolean,
        hasCircleForSelected: Boolean,
    },
    data() {
        return {

        };
    },
    computed: {
        hoverClass() {
            return (this.isHovered && !this.isSelected) ? 'bg-cm-100' : '';
        },
        selectedClass() {
            let selectedClasses = 'text-primary-600 font-semibold c-dropdown-options--selected';
            if (!this.hasCircleForSelected) {
                selectedClasses += ' bg-primary-100';
            }
            return this.isSelected
                ? selectedClasses
                : '';
        },
        styleClass() {
            return this.original.borderAbove ? 'c-dropdown-options__border' : '';
        },
        optionClasses() {
            return [
                this.optionClass,
                this.hoverClass,
                this.selectedClass,
                this.styleClass,
                this.sizeClass,
                this.hasGroupClass,
            ];
        },
        hasGroupClass() {
            return { 'c-dropdown-options--group': this.group };
        },
        sizeClass() {
            return `c-dropdown-options__size--${this.size}`;
        },
        optionClass() {
            return `c-dropdown-options--${this.position}`;
        },
    },
    methods: {
    },
    created() {

    },
};
</script>

<style scoped>

.c-dropdown-options {
    @apply
        flex
        items-center
        px-3
        py-1
    ;

    &:hover:not(.c-dropdown-options--selected) {
        @apply bg-cm-100;
    }

    &--last:not(.c-dropdown-options--group) {
        @apply pb-3;
    }

    &--first:not(.c-dropdown-options--group) {
        @apply pt-3;
    }

    &__size {
        &--sm {
            @apply
                text-xs
            ;
        }

        &--base {
            @apply
                text-xssm
            ;
        }
    }

    &__border {
        @apply
            border-cm-200
            border-solid
            border-t
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
            left-0.5
            rounded-full
            top-2.5
            w-2
        ;
    }
}

</style>
