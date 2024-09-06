<template>
    <div
        ref="pinsContainer"
        class="o-pin-items"
    >
        <FeatureItemsList v-bind="featureItemsProps">
            <template #itemsSlot="{ items }">
                <div class="flex gap-4">
                    <div
                        v-for="column in columns"
                        :key="column"
                        :style="columnWidth"
                    >
                        <FeatureItemsDraggable
                            class="flex flex-col gap-4"
                            v-bind="draggableProps(getColumnPins(items, column))"
                            @selectItem="openItemModal"
                        >
                        </FeatureItemsDraggable>
                    </div>
                </div>
            </template>

            <template #noContentSlot>
                <slot name="noContentSlot">
                </slot>
            </template>
        </FeatureItemsList>
    </div>
</template>

<script>
import interactsWithFeatureItems from '@/vue-mixins/features/interactsWithFeatureItems.js';
import listensToScrollandResizeEvents from '@/vue-mixins/listensToScrollAndResizeEvents.js';

export default {
    name: 'PinItems',
    components: {
    },
    mixins: [
        listensToScrollandResizeEvents,
        interactsWithFeatureItems,
    ],
    props: {
        pins: {
            type: [Array, null],
            required: true,
        },
        groupedPins: {
            type: [Object, null],
            required: true,
        },
    },
    emits: [
        'showMore',
        'openItem',
    ],
    data() {
        return {
            columnNumber: 1,
        };
    },
    computed: {
        columns() {
            return _.range(0, this.columnNumber);
        },
        columnWidth() {
            const percentage = 100 / this.columnNumber;
            return { width: `${percentage}%` };
        },
        columnsLength() {
            return this.columns.length;
        },

        // interactsWithFeatureItems.js mixin
        allItems() {
            return this.pins;
        },
        itemGroupings() {
            return this.getGroupings(this.pins, this.groupedPins);
        },
        viewType() {
            return 'LINE'; // Overwriting mixin until currentView is received as a prop. See TodoList.vue for example
        },
    },
    methods: {
        pinsInColumn(pinsArr) {
            const itemChunks = {};

            this.columns.forEach((column) => {
                itemChunks[column] = [];
            });

            pinsArr.forEach((pin, index) => {
                const whichColumn = index % this.columnsLength;

                itemChunks[whichColumn].push(pin);
            });
            return itemChunks;
        },
        getColumnPins(pinsArr, column) {
            const columns = this.pinsInColumn(pinsArr);
            return columns[column];
        },
        onResize() {
            const containerWidth = this.$refs.pinsContainer.getBoundingClientRect().width;
            if (containerWidth <= 400) {
                this.columnNumber = 1;
            } else if (containerWidth <= 700) {
                this.columnNumber = 2;
            } else if (containerWidth <= 900) {
                this.columnNumber = 3;
            } else {
                this.columnNumber = 4;
            }
        },

        // interactsWithFeatureItems.js mixin
        hasMore(grouping) {
            return this.hasMoreToLoad(grouping, this.pins, this.groupedPins);
        },
    },
    mounted() {
        this.onResize();
    },
};
</script>

<style scoped>
/* .o-pin-items {
} */
</style>
