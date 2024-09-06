<template>
    <div class="c-half-circle-graph flex flex-col items-center">
        <div class="header-2 mb-3">
            Completion pipeline
        </div>
        <ul
            class="c-half-circle-graph__graph z-over"
            :style="graphSize"
        >
            <li
                v-for="(value, index) in graphValues"
                :key="value.id"
                class="c-half-circle-graph__item"
                :class="colorClass(value.color)"
                :style="itemStyle(value.slice, index)"
            >
            </li>
        </ul>

        <GraphLegend
            class="mt-3"
            :graphValues="graphValues"
        >
        </GraphLegend>
    </div>
</template>

<script>

import GraphLegend from './GraphLegend.vue';

import providesColors from '@/vue-mixins/style/providesColors.js';

export default {
    name: 'HalfCircleGraph',
    components: {
        GraphLegend,
    },
    mixins: [
        providesColors,
    ],
    props: {
        graphValues: {
            type: Array,
            required: true,
        },
        height: {
            type: Number,
            default: 100,
        },
    },
    data() {
        return {

        };
    },
    computed: {
        width() {
            return this.height * 2;
        },
        graphSize() {
            return { width: `${this.width}px`, height: `${this.height}px` };
        },
        counts() {
            return this.graphValues.map((val) => {
                return val.count;
            });
        },
        valuesLength() {
            return this.graphValues.length;
        },
        total() {
            return _.sum(this.counts);
        },
        circleSlices() {
            return this.graphValues.map((val) => {
                return {
                    ...val,
                    slice: this.getRotation(val.count),
                };
            });
        },
    },
    methods: {
        getRotationStyle(deg) {
            return { transform: `rotate(${deg}deg)` };
        },
        getRotation(count) {
            const divided = count / this.total;
            return 180 * divided;
        },
        getFullRotation(rotation, index) {
            const numbers = _.range(0, (index + 1), 1);
            const arr = numbers.map((number) => {
                return this.circleSlices[number].slice;
            });
            const sum = _.sum(arr);
            return this.getRotationStyle(sum);
        },
        itemStyle(rotation, index) {
            const zIndex = this.valuesLength - index;
            return { ...this.getFullRotation(rotation, index), zIndex };
        },
        colorClass(color) {
            return this.getBorderColor(color.hue, color.intensity);
        },
    },
    created() {

    },
};
</script>

<style scoped>

.c-half-circle-graph {

    &__graph {
        @apply
            overflow-hidden
            relative
        ;

        &::before {
            content: '';

            @apply
                absolute
                border-b-0
                border-cm-200
                border-solid
                rounded-b-full
            ;
        }
    }

    &__item {
        backface-visibility: hidden;
        border-width: 35px;
        height: inherit;
        top: 100%;
        transform-origin: 50% 0;
        transform-style: preserve-3d;
        width: inherit;

        @apply
            absolute
            border-solid
            border-t-0
            left-0
            rounded-b-full
        ;
    }
}

</style>
