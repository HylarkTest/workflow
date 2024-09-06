<template>
    <button
        class="c-dropdown-display w-full"
        :class="displayClasses"
        type="button"
        v-on="selectedEvents"
    >
        <DropdownLabel
            v-if="inlineLabel && inlineLabel.position === 'inside'"
            :class="{ 'mr-2': !hideValue }"
            :label="inlineLabel"
        >
        </DropdownLabel>

        <div
            v-if="!hideValue"
            class="flex items-center flex-1 min-w-0"
        >
            <slot
                name="selected"
                :display="display"
                :original="original"
            >
                <span
                    class="u-ellipsis"
                    :class="textClasses"
                >
                    {{ display }}
                </span>
            </slot>

            <slot
                name="inlineDisplayAfter"
                :original="original"
            >
            </slot>
        </div>

        <div
            v-if="showDivider"
            class="c-dropdown-display__divider"
        >

        </div>

        <div
            v-if="!hideToggleButton"
            class="c-dropdown-display__angle centered bg-primary-100 text-primary-600"
        >
            <i
                class="far"
                :class="popupState
                    ? 'fa-angle-up'
                    : 'fa-angle-down'"
                title="Toggle dropdown"
            >
            </i>
        </div>
    </button>
</template>

<script>

import DropdownLabel from '@/components/dropdowns/DropdownLabel.vue';

export default {
    name: 'DropdownDisplay',
    components: {
        DropdownLabel,
    },
    mixins: [
    ],
    props: {
        inlineLabel: {
            type: Object,
            default: () => ({}),
        },
        display: {
            type: String,
            required: true,
        },
        original: {
            type: [String, Object, null],
            default: null,
        },
        popupState: Boolean,
        selectedEvents: {
            type: Object,
            required: true,
        },
        textColor: {
            type: String,
            default: 'base',
            validator(value) {
                return ['base', 'brand'].includes(value);
            },
        },
        borderColor: {
            type: String,
            default: 'none',
            validator(value) {
                return ['none', 'gray'].includes(value);
            },
        },
        placeholderColor: {
            type: String,
            default: 'gray',
            validator(value) {
                return ['gray', 'base'].includes(value);
            },
        },
        size: {
            type: String,
            default: 'base',
            validator(value) {
                return ['lg', 'base', 'sm'].includes(value);
            },
        },
        showDivider: Boolean,
        hideToggleButton: Boolean,
        hideValue: Boolean,
    },
    data() {
        return {

        };
    },
    computed: {
        textColorClass() {
            if (this.textColor === 'brand') {
                return 'text-primary-600';
            }
            return '';
        },
        sizeClass() {
            return `c-dropdown-display__size--${this.size}`;
        },
        displayClasses() {
            return [this.sizeClass, this.borderClass];
        },
        borderClass() {
            return this.popupState ? 'border-primary-600' : 'border-cm-300';
        },
        textClasses() {
            return !this.original ? this.placeholderColorClass : this.textColorClass;
        },
        placeholderColorClass() {
            return `c-dropdown-display__placeholder--${this.placeholderColor}`;
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

.c-dropdown-display {
    transition: border-color 0.2s ease-in-out;

    @apply
        flex
        items-center
        justify-between
    ;

    &__size {
        &--base {
            @apply
                text-sm
            ;
        }

        &--sm {
            @apply
                text-xssm
            ;
        }
    }

    &__divider {
        height: 18px;
        min-width: 1px;
        width: 1px;

        @apply
            bg-cm-200
            ml-2
            mr-1
        ;
    }

    &__angle {
        height: 16px;
        transition: 0.2s ease-in-out;
        width: 16px;

        @apply
            leading-none
            ml-2
            rounded
            text-sm
        ;

        &:hover {
            @apply
                bg-primary-200
            ;
        }
    }

    &__placeholder {
        &--gray {
            @apply
                text-cm-400
            ;
        }
    }
}

</style>
