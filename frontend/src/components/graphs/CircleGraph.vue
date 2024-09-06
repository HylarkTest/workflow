<template>
    <div class="c-circle-graph flex flex-col items-center justify-center h-full">

        <div class="header-2 mb-4 text-center">
            <slot name="header">
            </slot>
        </div>

        <slot
            name="extra"
        >
        </slot>

        <div class="relative">
            <ul
                class="c-circle-graph__graph z-over"
                :class="donutClass"
                :style="graphStyle"
            >
                <li
                    v-for="(value, index) in graphValues"
                    :key="value.id"
                    class="c-circle-graph__item"
                    :class="[colorClass(value.color), donutClass]"
                    :style="itemStyle(index)"
                >
                </li>
            </ul>

            <div
                v-if="showInnerCircle"
                class="c-circle-graph__center circle-center flex-col"
                :style="innerCircleStyle"
            >
                <div class="text-2xl font-bold text-cm-600">
                    12
                </div>
                <div class="text-cm-400 uppercase text-xs font-bold">
                    Total
                </div>
            </div>
        </div>

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
    name: 'CircleGraph',
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
        graphSize: {
            type: Number,
            default: 150,
        },
        borderWidth: {
            type: Number,
            default: 20,
        },
        innerCircleSize: {
            type: Number,
            default: 80,
        },
        showInnerCircle: Boolean,
        mode: {
            type: String,
            default: 'DONUT',
            validator(val) {
                return ['DONUT', 'PIE'].includes(val);
            },
        },
    },
    data() {
        return {

        };
    },
    computed: {
        isDonut() {
            return this.mode === 'DONUT';
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
                return this.getRotation(val.count);
            });
        },
        pixelBorder() {
            return `${this.borderWidth}px`;
        },
        pixelSize() {
            return `${this.graphSize}px`;
        },
        graphStyle() {
            return {
                height: this.pixelSize,
                width: this.pixelSize,
            };
        },
        innerPixelSize() {
            return `${this.innerCircleSize}px`;
        },
        // innerCirclePosition() {
        //     const position = this.graphSize / 2
        //     return `${position}px`
        // },
        innerCircleStyle() {
            return {
                height: this.innerPixelSize,
                width: this.innerPixelSize,
            };
        },
        borderStyle() {
            return { borderWidth: this.pixelBorder };
        },
        donutClass() {
            return this.isDonut ? 'border-solid' : '';
        },
    },
    methods: {
        /**
         * For the donut graph we want to use CSS to build each section by
         * clipping out the part of the ring that corresponds to the pie
         * section. We can also make use of CSS overlapping and z-index to only
         * clip out a section from 0 degrees to the end point of the segment,
         * instead of trying to clip just the slice which would be a lot more
         * complicated.
         * The tricky bit here is mapping the angles to the edges of a square
         * as the clip-path polygon function uses cartesian percentages.
         */
        clipPathFromAngle(segmentAngle) {
            const pi = Math.PI;
            const rads = (segmentAngle * pi) / 180;

            const topMiddleCoords = '50% 0%';
            const centerCoords = '50% 50%';
            const topRightCoords = '100% 0%';
            const bottomRightCoords = '100% 100%';
            const topLeftCoords = '0% 0%';
            const bottomLeftCoords = '0% 100%';

            // All paths cutting out a slice will start at the top of the square
            // and go to the center.
            let path = [topMiddleCoords, centerCoords];
            // Is the segment angle pointing to the right side of the triangle?
            if (segmentAngle >= 45 && segmentAngle < 135) {
                const y = 50 * (1 - Math.tan((0.5 * pi) - rads));
                // Work out the coordinates on the right side of the square and
                // then finish up the path by going to the top right corner.
                path = path.concat([`100% ${y}%`, topRightCoords]);
                // Is the segment angle pointing to the bottom side of the triangle?
            } else if (segmentAngle >= 135 && segmentAngle < 225) {
                const x = 50 * (1 + Math.tan(pi - rads));
                // Work out the coordinates on the bottom side of the square and
                // then finish up the path by going to the bottom right, then
                // the top right corner.
                path = path.concat([`${x}% 100%`, bottomRightCoords, topRightCoords]);
                // Is the segment angle pointing to the left side of the triangle?
            } else if (segmentAngle >= 225 && segmentAngle < 295) {
                const y = 50 * (1 + Math.tan((1.5 * pi) - rads));
                // Work out the coordinates on the left side of the square and
                // then finish up the path by going to the bottom left, then
                // the bottom right corner, then the top right corner.
                path = path.concat([`0% ${y}%`, bottomLeftCoords, bottomRightCoords, topRightCoords]);
                // Is the segment angle pointing to the top side of the triangle?
            } else if (segmentAngle >= 295 || segmentAngle < 45) {
                const x = 50 * (1 - Math.tan((2 * pi) - rads));
                path = path.concat([`${x}% 0%`, topLeftCoords, bottomLeftCoords, bottomRightCoords, topRightCoords]);
            }
            return path;
        },
        getRotation(count) {
            const divided = count / this.total;
            return 360 * divided;
        },
        getClipPath(index) {
            const numbers = _.range(0, (index + 1), 1);
            const arr = numbers.map((number) => {
                return this.circleSlices[number];
            });
            const sum = _.sum(arr);
            const path = this.clipPathFromAngle(sum);
            return { clipPath: `polygon(${path.join(', ')})` };
        },
        itemStyle(index) {
            const zIndex = this.valuesLength - index;
            return { ...this.getClipPath(index), zIndex, ...this.borderStyle };
        },
        colorClass(color) {
            if (this.isDonut) {
                return this.getBorderColor(color.hue, color.intensity);
            }
            return this.getBgColor(color.hue, color.intensity);
        },
    },
    created() {

    },
};
</script>

<style scoped>

.c-circle-graph {
    &__graph {
        @apply
            overflow-hidden
            relative
        ;

        &::before {
            content: '';
            height: inherit;
            width: inherit;

            @apply
                absolute
                border-cm-200
                rounded-full
            ;
        }
    }

    &__item {
        backface-visibility: hidden;
        height: inherit;
        transform-origin: 50% 50%;
        transform-style: preserve-3d;
        width: inherit;

        @apply
            absolute
            left-0
            rounded-full
            top-0
        ;
    }

    &__center {
        left: 50%;
        top:  50%;
        transform: translate(-50%, -50%);

        @apply
            absolute
            bg-cm-200
        ;
    }
}

</style>
