<template>
    <div
        class="c-displayer-money flex"
        :class="displayClasses"
    >
        <template
            v-if="displayFieldValue"
        >
            {{ currencySymbol }}
            {{ formattedAmount }}
        </template>
    </div>
</template>

<script>

import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';

import interactsWithMoneyDisplayers from '@/vue-mixins/displayers/interactsWithMoneyDisplayers.js';

import useMoneyFormat from '@/composables/useMoneyFormat.js';

export default {
    name: 'DisplayerMoney',
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
            typeKey: 'MONEY',
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

/*.c-displayer-money {

} */

</style>
