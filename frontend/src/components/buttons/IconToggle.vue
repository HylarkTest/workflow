<template>
    <div
        class="c-icon-toggle bg-primary-200"
        :class="sizeClass"
    >
        <div
            class="c-icon-toggle__slider bg-primary-600"
            :style="sliderPosition"
        >

        </div>

        <button
            v-for="(option, index) in options"
            :key="option.val"
            class="c-icon-toggle__button centered text-primary-600 hover:text-primary-500"
            :class="selectedIconClass(option)"
            type="button"
            :title="$t(option.langPath)"
            @click="selectOption(option, index)"
        >
            <i
                :class="option.icon"
            >
            </i>
        </button>
    </div>
</template>

<script>

export default {
    name: 'IconToggle',
    components: {
    },
    mixins: [
    ],
    props: {
        options: {
            type: Array,
            required: true,
        },
        modelValue: {
            type: String,
            required: true,
        },
        size: {
            type: String,
            default: 'base',
            validator(value) {
                return ['base', 'sm'].includes(value);
            },
        },
    },
    emits: [
        'update:modelValue',
    ],
    data() {
        return {

        };
    },
    computed: {
        indexOfSelected() {
            return _.findIndex(this.options, { val: this.modelValue });
        },
        sliderPosition() {
            const position = (this.indexOfSelected * this.sliderSize) + 3;
            const pixels = `${position}px`;
            return { left: pixels };
        },
        sliderSize() {
            if (this.size === 'sm') {
                return 34;
            }
            return 40;
        },
        sizeClass() {
            return `c-icon-toggle--${this.size}`;
        },
    },
    methods: {
        selectedIconClass(option) {
            if (this.isSelectedIcon(option)) {
                return 'c-icon-toggle__button--selected no-color-hover';
            }
            return '';
        },
        isSelectedIcon(option) {
            return option.val === this.modelValue;
        },
        selectOption(option, index) {
            let payload;
            if (this.isSelectedIcon(option)) {
                const nextIndex = (index === (this.options.length - 1)) ? 0 : index + 1;
                const next = this.options[nextIndex];
                payload = next.val;
            } else {
                payload = option.val;
            }
            this.$emit('update:modelValue', payload);
        },
    },
    created() {

    },
};
</script>

<style scoped>

.c-icon-toggle {
    padding:  3px;

    @apply
        flex
        items-center
        relative
        rounded-full
    ;

    &--base {
        .c-icon-toggle__button {
            width:  40px;
        }

        .c-icon-toggle__slider {
            width:  40px;
        }
    }

    &--sm {
        @apply
            text-sm
        ;

        .c-icon-toggle__button {
            width:  36px;
        }

        .c-icon-toggle__slider {
            width:  36px;
        }
    }

    &__button {
        padding: 5px 3px 3px 3px;
        transition: 0.2s ease-in-out;

        @apply
            leading-none
            relative
        ;

        &--selected {
            @apply
                text-cm-00
            ;
        }
    }

    &__slider {
        height: calc(100% - 6px);
        top: 3px;
        transition: 0.2s ease-in-out;

        @apply
            absolute
            rounded-full
        ;
    }
}

</style>
