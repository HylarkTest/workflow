<template>
    <div
        class="c-toggle-button"
        :class="[disabledClass, sizeClass]"
        @click="emitValue"
    >
        <input
            ref="button"
            type="checkbox"
            :checked="isChecked"
            :disabled="disabled"
        />
        <span
            class="c-toggle-button__slider"
            :class="sliderActiveClass"
        >

        </span>
    </div>
</template>

<script>
import formWrapperChild from '@/vue-mixins/formWrapperChild.js';

export default {

    name: 'ToggleButton',
    components: {

    },
    mixins: [
        formWrapperChild,
    ],
    props: {
        disabled: Boolean,
        val: {
            type: [String, Object],
            default: '',
        },
        predicate: {
            type: [String, Function, null],
            default: () => _.identity,
        },
        size: {
            type: String,
            default: 'base',
            validator(sizeKey) {
                return ['base', 'sm'].includes(sizeKey);
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
        sliderActiveClass() {
            return { 'c-toggle-button__slider--active': this.isChecked };
        },
        disabledClass() {
            return { 'pointer-events-none opacity-50': this.disabled };
        },
        sizeClass() {
            return `c-toggle-button--${this.size}`;
        },
        isChecked() {
            if (_.isBoolean(this.formValue)) {
                return this.formValue;
            }
            return (this.compare(this.val, this.formValue))
                || (_.isArray(this.formValue)
                    && (this.formValue.some((value) => this.compare(this.val, value))))
                || (_.isPlainObject(this.formValue) && this.formValue[this.val]);
        },
    },
    methods: {
        compare(val, value) {
            let valCompare;
            let valueCompare;
            if (_.isString(this.predicate)) {
                valCompare = val && val[this.predicate];
                valueCompare = value && value[this.predicate];
            } else {
                valCompare = this.predicate(val);
                valueCompare = this.predicate(value);
            }
            return valCompare === valueCompare;
        },
        emitValue() {
            if (!this.disabled) {
                if (this.val && this.formValue) {
                    if (Array.isArray(this.formValue)) {
                        const index = this.predicate !== _.identity
                            ? _.findIndex(this.formValue, (value) => this.compare(value, this.val))
                            : this.formValue.findIndex((item) => (item instanceof Proxy
                                ? item.target
                                : item) === this.val);
                        if (~index) {
                            this.emitInput([
                                ...this.formValue.slice(0, index),
                                ...this.formValue.slice(index + 1),
                            ]);
                        } else {
                            this.emitInput([
                                ...this.formValue,
                                this.val,
                            ]);
                        }
                    } else {
                        this.emitInput(
                            // Unsure
                            _.chain(this.formValue).clone().set(this.val, this.$refs.button.checked).value()
                        );
                    }
                } else {
                    this.emitInput(!this.formValue);
                }
            }
        },
    },
    created() {

    },
};
</script>

<style scoped>
.c-toggle-button {
    min-width: 40px;
    width: 40px;

    @apply
        h-6
        inline-block
        relative
    ;

    &__input {
        @apply
            h-0
            opacity-0
            w-0
        ;
    }

    &__slider {
        transition: 0.2s ease-in-out;
        @apply
            absolute
            bg-cm-300
            bottom-0
            cursor-pointer
            left-0
            right-0
            rounded-full
            top-0
        ;

        &:hover {
            @apply bg-cm-200;
        }

        &::before {
            bottom: 4px;
            content: "";
            left: 4px;
            transition: 0.2s ease-in-out;

            @apply
                absolute
                bg-cm-00
                h-4
                rounded-full
                w-4
            ;
        }

        &--active {
            @apply
                bg-primary-600
            ;

            &:hover {
                @apply bg-primary-500;
            }

            &::before {
                transform: translateX(1rem);
            }
        }
    }

    &--sm {
        min-width: 28px;
        width: 28px;

        @apply
            h-4
        ;

        .c-toggle-button__slider {
            &::before {
                bottom: 1px;
                left: 1px;

                @apply
                    h-3.5
                    w-3.5
                ;
            }

            &--active {
                &::before {
                    transform: translateX(12px);
                }
            }
        }
    }
}
</style>
