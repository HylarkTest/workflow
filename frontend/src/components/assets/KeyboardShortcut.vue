<template>
    <div class="c-keyboard-shortcut flex">
        <KeyDisplay
            v-for="symbol in shortcut"
            :key="symbol"
            class="c-keyboard-shortcut__key"
            :symbol="symbol"
        >
        </KeyDisplay>
    </div>
</template>

<script>

import KeyDisplay from './KeyDisplay.vue';

export default {
    name: 'KeyboardShortcut',
    components: {
        KeyDisplay,
    },
    mixins: [
    ],
    props: {
        shortcut: {
            type: Array,
            required: true,
        },
    },
    data() {
        return {

        };
    },
    computed: {

    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

.c-keyboard-shortcut {
    &__key:not(:first-child) {
        @apply
            ml-1
        ;
    }
}

</style>
