<template>
    <div
        v-if="displayFieldValue"
        class="c-displayer-number-range text-sm"
    >
        <div
            class="flex items-center text-sm"
            :class="displayClasses"
        >
            {{ from }}
            <template
                v-if="!to"
            >
                +
            </template>
            <span
                v-if="from && to"
                class="block mx-0.5"
            >
                -
            </span>
            <template
                v-if="!from"
            >
                &lt;
            </template>
            {{ to }}
        </div>
    </div>
</template>

<script>

import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';

export default {
    name: 'DisplayerNumberRange',
    components: {

    },
    mixins: [
        interactsWithDisplayers,
    ],
    props: {
    },
    data() {
        return {
            typeKey: 'NUMBER_RANGE',
        };
    },
    computed: {
        from() {
            return this.displayFieldValue.from;
        },
        to() {
            return this.displayFieldValue.to;
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.c-displayer-number-range {

} */

</style>
