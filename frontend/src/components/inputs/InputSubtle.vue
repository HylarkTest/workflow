<template>
    <div
        class="c-input-subtle"
        :class="displayClasses"
    >
        <InputField
            ref="input"
            :inputClass="inputClass"
            v-bind="$attrs"
            @onFocus="setFocus"
        >
        </InputField>
    </div>
</template>

<script>

import InputField from '@/components/inputs/InputField.vue';

export default {
    name: 'InputSubtle',
    components: {
        InputField,
    },
    mixins: [
    ],
    props: {
        paddingClass: {
            type: String,
            default: 'py-0.5 px-1',
        },
        displayClasses: {
            type: String,
            default: '',
        },
        focusClasses: {
            type: String,
            default: 'bg-cm-00 shadow-primary-700/30',
        },
        alwaysHighlighted: Boolean,
        neverHighlighted: Boolean,
    },
    data() {
        return {
            inFocus: false,
        };
    },
    computed: {
        inputClass() {
            return `${this.paddingClass} ${this.focusClass} c-input-subtle__input`;
        },
        focusClass() {
            const classes = `c-input-subtle__input--focused ${this.focusClasses}`;
            if (this.neverHighlighted) {
                return '';
            }
            if (this.alwaysHighlighted) {
                return classes;
            }
            return this.inFocus ? classes : '';
        },

    },
    methods: {
        setFocus(focus) {
            this.inFocus = focus;
        },
        focus() {
            this.$refs.input.focus();
        },
        select() {
            this.$refs.input.select();
        },
    },
    created() {

    },
};
</script>

<style>

.c-input-subtle {
    &__input {
        @apply
            mb-0.5
            rounded-lg
        ;

        &--focused {
            @apply
                shadow-lg
            ;
        }
    }
}

</style>
