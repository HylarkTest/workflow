<template>
    <div class="o-appearance-accent">

        <ButtonEl
            v-for="color in accentColors"
            :key="color.val"
            class="o-appearance-accent__circle circle-center"
            :class="selectedClass(color)"
            :style="styleObj(color)"
            @click="selectColor(color)"
        >
            <i
                class="fas fa-check text-white transition-2eio"
                :class="isSelected(color) ? 'opacity-100' : 'opacity-0'"
            >
            </i>
        </ButtonEl>
    </div>
</template>

<script>

import { accentColors } from '@/core/display/accentColors.js';

export default {
    name: 'AppearanceAccent',
    components: {
    },
    mixins: [
    ],
    props: {
        modelValue: {
            type: String,
            required: true,
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
        scheme() {
            return this.$root.isInDarkMode ? 'dark' : 'light';
        },
    },
    methods: {
        isSelected(color) {
            return this.modelValue === color.val;
        },
        styleObj(color) {
            return {
                backgroundColor: color[this.scheme].main,
            };
        },
        selectedClass(color) {
            if (this.isSelected(color)) {
                return 'o-appearance-accent__circle--selected';
            }
            return '';
        },
        selectColor(color) {
            this.$emit('update:modelValue', color.val);
        },
    },
    created() {
        this.accentColors = accentColors;
    },
};
</script>

<style scoped>

.o-appearance-accent {
    max-width: 600px;

    @apply
        flex
        flex-wrap
        -m-3
    ;

    &__circle {
        font-size: 15px;
        transition: 0.2s ease-in-out;

        @apply
            border
            h-8
            m-3
            w-8
        ;

        &:hover {
            @apply
                shadow-center-dark
            ;
        }

        &--selected {
            transform: scale(1.2);

            @apply
                shadow-center-dark
            ;
        }
    }
}

</style>
