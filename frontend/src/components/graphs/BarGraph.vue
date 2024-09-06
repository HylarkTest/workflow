<template>
    <div class="c-bar-graph">

        <h3
            v-if="hasTopHeader"
            class="c-bar-graph__header--top header-2 c-bar-graph__header"
            :class="headerAlignmentClass"
        >
            <slot name="header">
            </slot>
        </h3>

        <div class="flex w-full relative">
            <div
                v-if="yAxisLabel"
                class="c-bar-graph__label--y c-bar-graph__label"
            >
                {{ yAxisLabel }}
            </div>
            <div class="w-full">
                <div
                    class=""
                    :class="graphClasses"
                >
                    <div class="flex">
                        <div class="flex flex-col-reverse justify-between mr-2">
                            <div
                                v-for="(label, index) in yLabels"
                                :key="index"
                                class="text-sm font-medium text-cm-700 relative"
                            >
                                {{ label }}

                                <div
                                    v-if="!(index === 0 || index === lastYLabelIndex)"
                                    class="h-px w-3 absolute bg-cm-400 -right-4 top-2"
                                >
                                </div>
                            </div>
                        </div>
                        <div>

                        </div>
                        <div
                            class="w-full flex items-end border-b border-l border-cm-300 border-solid"
                            :style="chartHeight"
                        >
                            <div
                                v-for="val in graphValues"
                                :key="val.xVal"
                                class="h-full flex justify-center items-end"
                                :style="xWidth"
                            >
                                <div
                                    class="bg-primary-600 w-6 rounded-t-md"
                                    :style="yStyle(val.yVal)"
                                >
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex mt-1 ml-4">
                        <div
                            v-for="label in xLabels"
                            :key="label.id"
                            class="text-center text-xs px-1 font-medium text-cm-700"
                            :style="xWidth"
                        >
                            {{ label.display }}
                        </div>
                    </div>

                </div>
                <div
                    v-if="xAxisLabel"
                    class="c-bar-graph__label--x c-bar-graph__label"
                >
                    {{ xAxisLabel }}
                </div>
            </div>
        </div>

        <h3
            v-if="hasBottomHeader"
            class="c-bar-graph__header--bottom header-2 c-bar-graph__header"
            :class="headerAlignmentClass"
        >
            <slot name="header">
            </slot>
        </h3>
    </div>
</template>

<script>

export default {
    name: 'BarGraph',
    components: {

    },
    mixins: [
    ],
    props: {
        headerPlacement: {
            type: [String, Boolean],
            default: 'TOP',
            validator(val) {
                return ['TOP', 'BOTTOM'].includes(val) || !val;
            },
        },
        headerAlignment: {
            type: String,
            default: 'LEFT',
            validator(val) {
                return ['RIGHT', 'CENTER', 'LEFT'].includes(val);
            },
        },
        yAxisLabel: {
            type: [String, Boolean],
            required: true,
        },
        xAxisLabel: {
            type: [String, Boolean],
            required: true,
        },
        xValType: {
            type: String,
            required: true,
        },
        graphValues: {
            type: Array,
            required: true,
        },
        height: {
            type: Number,
            default: 120,
        },
    },
    data() {
        return {

        };
    },
    computed: {
        chartHeight() {
            return { height: `${this.height}px` };
        },
        graphClasses() {
            return `${this.graphPaddingClass}`;
        },
        graphPaddingClass() {
            return this.yAxisLabel ? 'pl-8' : '';
        },
        dataPoints() {
            return this.graphValues.length;
        },
        displayFunctionKey() {
            const val = _.camelCase(this.xValType);
            return `${val}Display`;
        },
        xDivision() {
            return 100 / this.dataPoints;
        },
        xWidth() {
            return { width: `${this.xDivision}%` };
        },
        xLabels() {
            return this.graphValues.map((val) => {
                return {
                    id: val.xVal,
                    display: this.getDisplay(val.xVal),
                };
            });
        },
        yLabelsArr() {
            return this.graphValues.map((val) => {
                return val.yVal;
            });
        },
        yMax() {
            return _.max(this.yLabelsArr);
        },
        yMin() {
            return 0;
        },
        yDifference() {
            return this.yMax - this.yMin;
        },
        newYMax() {
            return this.yMax + this.step;
        },
        desiredSteps() {
            return 4;
        },
        step() {
            const val = this.yDifference / this.desiredSteps;
            return Math.floor(val);
        },
        yLabels() {
            const max = this.newYMax + this.step; // Because lodash does the steps below the max
            return _.range(0, max, this.step);
        },
        yLabelsLength() {
            return this.yLabels.length;
        },
        lastYLabelIndex() {
            return this.yLabels.length - 1;
        },
        hasTopHeader() {
            return this.headerPlacement === 'TOP';
        },
        hasBottomHeader() {
            return this.headerPlacement === 'BOTTOM';
        },
        headerAlignmentClass() {
            const val = _.camelCase(this.headerAlignment);
            return `text-${val}`;
        },
    },
    methods: {
        getDisplay(val) {
            return this[this.displayFunctionKey](val);
        },
        weeklyRangeDisplay(val) {
            const start = this.$dayjs(val).format('D MMM');
            const end = this.$dayjs(val).add(6, 'days').format('D MMM');
            return `${start} - ${end}`;
        },
        yStyle(yVal) {
            const percentage = (yVal / this.newYMax) * 100;
            return { height: `${percentage}%` };
        },
    },
    created() {

    },
};
</script>

<style scoped>

.c-bar-graph {
    @apply
        relative
    ;

    &__header {
        &--top {
            @apply
                mb-4
            ;
        }
        &--bottom {
            @apply
                mt-4
            ;
        }
    }

    &__label {
        @apply
            font-semibold
            text-center
            text-sm
        ;

        &--y {
            bottom: 40px;
            transform: rotate(-90deg);
            transform-origin: 0 0;

            @apply
                absolute
                left-0
            ;
        }

        &--x {
            @apply
                mt-2
            ;
        }
    }
}

</style>
