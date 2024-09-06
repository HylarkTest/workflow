<template>
    <div
        class="c-displayer-salary flex"
        :class="displayClasses"
    >
        <template
            v-if="displayFieldValue"
        >
            {{ currencySymbol }}
            {{ formattedAmount }}
            / {{ periodString }}
        </template>
    </div>
</template>

<script>

import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';

import interactsWithMoneyDisplayers from '@/vue-mixins/displayers/interactsWithMoneyDisplayers.js';

import interactsWithSalaryDisplayers from '@/vue-mixins/displayers/interactsWithSalaryDisplayers.js';

import useMoneyFormat from '@/composables/useMoneyFormat.js';

export default {
    name: 'DisplayerSalary',
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
            typeKey: 'SALARY',
        };
    },
    computed: {
        formattedAmount() {
            return this.formatMoneyForDisplay(this.amount);
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.c-displayer-salary {

} */

</style>
