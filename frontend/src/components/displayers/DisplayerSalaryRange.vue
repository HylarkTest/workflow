<template>
    <div
        v-if="displayFieldValue"
        class="c-displayer-salary-range flex flex-wrap"
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
        /{{ periodString }}
    </div>
</template>

<script>

import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';

import interactsWithMoneyDisplayers from '@/vue-mixins/displayers/interactsWithMoneyDisplayers.js';

import interactsWithSalaryDisplayers from '@/vue-mixins/displayers/interactsWithSalaryDisplayers.js';

import useMoneyFormat from '@/composables/useMoneyFormat.js';

export default {
    name: 'DisplayerSalaryRange',
    components: {

    },
    mixins: [
        interactsWithDisplayers,
        interactsWithMoneyDisplayers,
        interactsWithSalaryDisplayers,
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
            typeKey: 'SALARY_RANGE',
        };
    },
    computed: {
        periodString() {
            const formatted = _.camelCase(this.displayFieldValue.period);
            return this.$t(`labels.salaryPeriods.${formatted}`);
        },
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

/*.c-displayer-salary-range {

} */

</style>
