<template>
    <div
        class="c-textarea-field transition-2eio"
        :class="displayClasses"
    >
        <label
            v-if="$slots.default"
            class="c-textarea-field__label"
            :class="{ 'c-textarea-field__label--focus': inFocus }"
        >
            <slot></slot>
        </label>
        <textarea
            ref="focus"
            class="w-full c-textarea-field__area"
            :class="[resizeClass, bgClass]"
            :style="{ height: height }"
            :value="formValue"
            :maxLength="maxLength"
            v-bind="$attrs"
            @focus="inFocus = true"
            @blur="inFocus = false"
            @input="emitInput($event.target.value)"
        >
        </textarea>
        <CharactersRemaining
            positioningClasses="absolute -bottom-3.5 right-0"
            :maxLength="maxLength"
            :length="formValueLength"
        >
        </CharactersRemaining>
    </div>
</template>

<script>

import CharactersRemaining from '@/components/assets/CharactersRemaining.vue';
import formWrapperChild from '@/vue-mixins/formWrapperChild.js';

export default {

    name: 'TextareaField',
    components: {
        CharactersRemaining,
    },
    mixins: [
        formWrapperChild,
    ],
    props: {
        height: {
            type: String,
            default: '100px',
        },
        hideResize: Boolean,
        bgColor: {
            type: String,
            default: 'white',
            validator(value) {
                return ['white', 'gray'].includes(value);
            },
        },
        boxStyle: {
            type: String,
            default: 'border',
            validator(value) {
                return ['plain', 'border'].includes(value);
            },
        },
        maxLength: {
            type: Number,
            default: 2000,
        },
        size: {
            type: String,
            default: 'base',
            validator(val) {
                return ['base', 'sm', 'md'].includes(val);
            },
        },
    },
    emits: [
        'input',
    ],
    data() {
        return {
            inFocus: false,
        };
    },
    computed: {
        resizeClass() {
            return this.hideResize ? 'resize-none' : '';
        },
        bgClass() {
            return `c-textarea-field__bg--${this.bgColor}`;
        },
        boxStyleClass() {
            return `c-textarea-field__style--${this.boxStyle}`;
        },
        extraClasses() {
            return this.inFocus
                ? 'c-textarea-field--focus shadow-primary-600/20'
                : 'c-textarea-field--hover shadow-primary-600/20';
        },
        displayClasses() {
            return `${this.extraClasses} ${this.bgClass} ${this.boxStyleClass} ${this.sizeClass}`;
        },
        formValueLength() {
            return this.formValue?.length || 0;
        },
        sizeClass() {
            return `c-textarea-field__size--${this.size}`;
        },
    },
    methods: {
        focus() {
            this.$refs.focus.focus();
        },
        select() {
            this.$refs.focus.select();
        },
    },
    created() {

    },
};
</script>

<style scoped>
.c-textarea-field {
    @apply
        flex
        flex-col
        leading-tight
        p-2
        relative
        rounded-lg
        text-base
        z-0
    ;

    &__bg {
        &--gray {
            @apply
                bg-cm-100
            ;
        }
        &--white {
            @apply
                bg-cm-00
            ;
        }
    }

    &__style {
        &--border {
            @apply
                border
                border-cm-300
                border-solid
            ;
        }
    }

    &__label {
        @apply
            absolute
            bg-cm-00
            left-2
            px-1
            text-cm-600
            text-xs
            -top-2
        ;

        &--focus {
            @apply
                text-primary-600
            ;
        }
    }

    &__size {
        &--sm {
            @apply
                p-1
                text-xs
            ;
        }
        &--md {
            @apply
                p-1
                text-xssm
            ;
        }
    }

    &--hover:hover.c-textarea-field__style--border {
        @apply
            border-cm-500
        ;

    }
    &--hover:hover.c-textarea-field__style--plain {
        @apply shadow-md;
    }

    &--focus.c-textarea-field__style--border {
        @apply border-primary-600;
    }
    &--focus.c-textarea-field__style--plain {
        @apply shadow-lg;
    }

    &__area {
        min-height:  30px;
    }

}
</style>
