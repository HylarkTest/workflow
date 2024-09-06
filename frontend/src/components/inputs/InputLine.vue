<template>
    <div
        class="c-input-line"
        :class="[disabledClass, bgClass, inputElClasses]"
    >
        <label
            v-if="$slots.label"
            class="c-input-line__label"
            :class="labelClasses"
        >
            <slot name="label">
            </slot>
        </label>

        <label
            v-if="$slots.label && motion"
            class="c-input-line__label c-input-line__label--motion transition-2eio"
            :class="{ 'c-input-line__label--val': hasValue }"
        >
            <slot name="label">
            </slot>
        </label>

        <InputField
            ref="input"
            :inputClass="inputClasses"
            :disabled="disabled"
            v-bind="$attrs"
            @onFocus="onFocus"
        >
            <template
                #afterInput
            >
                <slot
                    name="afterInput"
                >
                </slot>
            </template>
        </InputField>
        <ClearButton
            v-if="showClear"
            positioningClass="absolute bottom-2 right-0"
            @click="$emit('clearInput')"
        >
        </ClearButton>
    </div>
</template>

<script>

import InputField from '@/components/inputs/InputField.vue';
import interactsWithFormWrapperValue from '@/vue-mixins/interactsWithFormWrapperValue.js';
import ClearButton from '@/components/buttons/ClearButton.vue';

export default {

    name: 'InputLine',
    components: {
        InputField,
        ClearButton,
    },
    mixins: [
        interactsWithFormWrapperValue,
    ],
    inheritAttrs: false,
    props: {
        showClear: Boolean,
        disabled: Boolean,
        motion: Boolean,
        bgColor: {
            type: String,
            default: 'transparent',
            validator(val) {
                return ['white', 'transparent', 'gray'].includes(val);
            },
        },
        borderWidthClass: {
            type: String,
            default: 'border-b-2',
        },
        inputElClasses: {
            type: String,
            default: '',
        },
    },
    emits: [
        'clearInput',
    ],
    data() {
        return {
            inFocus: false,
        };
    },
    computed: {
        clearClass() {
            return this.showClear ? 'c-input-line__input--clear' : '';
        },
        disabledClass() {
            return { 'opacity-50 no-pointer': this.disabled };
        },
        labelClasses() {
            return [this.focusClass, this.fixedClass];
        },
        fixedClass() {
            return { 'c-input-line__label--fixed': this.motion };
        },
        focusClass() {
            return { 'c-input-line__label--focus': !this.motion && this.inFocus };
        },
        borderClasses() {
            return this.inFocus ? 'c-input-line__input--focus' : 'c-input-line__input--hover';
        },
        inputClasses() {
            return `c-input-line__input ${this.borderClasses} ${this.clearClass} ${this.borderWidthClass}`;
        },
        hasValue() {
            return this.inFocus || this.formValue;
        },
        bgClass() {
            return `c-input-line__bg--${this.bgColor}`;
        },
    },
    methods: {
        onFocus(focusState) {
            this.inFocus = focusState;
            if (focusState) {
                this.focus();
            }
        },
        focus() {
            this.$refs.input?.focus();
        },
        select() {
            this.$refs.input?.select();
        },
    },
    created() {

    },
};
</script>

<style>
.c-input-line {
    @apply
        relative
        rounded-t-lg
    ;

    &__label {
        @apply
            block
            mb-1
            text-cm-800
            text-xs
        ;

        &--fixed {
            @apply
                invisible
            ;
        }

        &--motion {
            left: 8px;
            top: 18px;

            @apply
                absolute
                text-base
                text-cm-500
            ;
        }

        &--val {
            left: 0;
            top: -2px;

            @apply
                text-cm-800
                text-xs
            ;
        }
    }

    &__bg {
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

    &__input {
        @apply
            border-cm-300
            border-solid
            pb-1
            px-2
        ;

        &--clear {
            @apply
                pr-4
            ;
        }

        &--hover:hover {
            @apply
                border-cm-500
            ;
        }

        &--focus {
            @apply
                border-primary-600
            ;
        }
    }
}
</style>
