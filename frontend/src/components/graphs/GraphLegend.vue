<template>
    <div class="c-graph-legend flex flex-wrap justify-center -m-1">
        <div
            v-for="value in graphValues"
            :key="value.id"
            class="flex items-center py-1 px-2"
        >
            <div
                class="h-3 w-3 mr-1.5 rounded"
                :class="getBgColor(value.color.hue, value.color.intensity)"
            >

            </div>

            <div
                class="text-xs font-medium"
            >
                {{ text(value) }}
            </div>
        </div>
    </div>
</template>

<script>

import providesColors from '@/vue-mixins/style/providesColors.js';

export default {
    name: 'GraphLegend',
    components: {
    },
    mixins: [
        providesColors,
    ],
    props: {
        graphValues: {
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
        text(val) {
            return val.textPath ? this.$t(val.textPath) : val.text;
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.c-graph-legend {

} */

</style>
