<template>
    <div
        v-if="displayFieldValue"
        class="c-displayer-money-range flex"
        :class="displayClasses"
    >
        <template
            v-if="!from"
        >
            &lt;
        </template>
        <span>
            {{ currencySymbol }}
        </span>
        <span>
            {{ fromFormatted }}
        </span>
        <span
            v-if="from && to"
            class="block mx-0.5"
        >
            -
        </span>
        <template
            v-if="!to"
        >
            +
        </template>
        {{ toFormatted }}
    </div>
</template>

<script>

import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';

import interactsWithMoneyDisplayers from '@/vue-mixins/displayers/interactsWithMoneyDisplayers.js';

import useMoneyFormat from '@/composables/useMoneyFormat.js';

export default {
    name: 'DisplayerMoneyRange',
    components: {

    },
    mixins: [
        interactsWithDisplayers,
        interactsWithMoneyDisplayers,
    ],
    props: {

    },
    setup() {
        const {
            formatMoneyForDisplay,
        } = useMoneyFormat();

        return {
            formatMoneyForDisplay,
        };
    },
    data() {
        return {
            typeKey: 'MONEY_RANGE',
        };
    },
    computed: {
        toFormatted() {
            return this.formatMoneyForDisplay(this.to);
        },
        fromFormatted() {
            return this.formatMoneyForDisplay(this.from);
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.c-displayer-money-range {

} */

</style>
