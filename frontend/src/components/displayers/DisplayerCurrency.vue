<template>
    <div
        class="c-displayer-currency"
        :class="displayClasses"
    >
        <div
            class="list-value-parent"
        >
            <ValueBasic
                v-for="val in dataValueArr"
                :key="val"
                :hideRemove="true"
            >
                {{ formatCode(val) }}
            </ValueBasic>
        </div>
    </div>
</template>

<script>

import interactsWithMultiDisplayers from '@/vue-mixins/displayers/interactsWithMultiDisplayers.js';
import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';
import { formatCode } from '@/core/helpers/currencyHelpers.js';

export default {
    name: 'DisplayerCurrency',
    components: {

    },
    mixins: [
        interactsWithMultiDisplayers,
        interactsWithDisplayers,
    ],
    props: {

    },
    data() {
        return {
            typeKey: 'CURRENCY',
        };
    },
    computed: {
        fieldValue() {
            return this.displayFieldValue;
        },
    },
    methods: {
        formatCode(code) {
            return formatCode(code);
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.c-displayer-currency {

} */

</style>
