<template>
    <Teleport
        v-if="parentRefs"
        :to="parentRefs.container"
        :disabled="!parentRefs.container"
    >
        <div
            class="pointer-events-none top-0 left-0 absolute z-over h-full overflow-hidden"
            :style="{ width: `${firstColWidth + 8}px` }"
        >
            <div
                class="shadow-xl h-full w-full"
                :style="{ width: `${firstColWidth}px` }"
            >

            </div>
        </div>
    </Teleport>

    <Teleport
        v-if="parentRefs"
        :to="parentRefs.spreadsheet"
        :disabled="!parentRefs.spreadsheet"
    >
        <div
            v-if="dragging && dragging.rightMargin"
            class="c-spreadsheet-header__line bg-primary-400"
            :style="{ left: dragging.rightMargin + 'px' }"
        >

        </div>
    </Teleport>

    <thead class="c-spreadsheet-header">
        <Draggable
            :modelValue="gridMap"
            tag="tr"
            itemKey="id"
            :disabled="!isDraggable"
            handle=".drag-this"
            :move="({ relatedContext: { index } }) => index !== 0"
            @update:modelValue="updateColumns"
        >
            <template
                #item="{ element: column, index }"
            >
                <th
                    :ref="setRef(column)"
                    scope="col"
                    class="c-spreadsheet-header__column bg-primary-200"
                    :class="{ 'hover:bg-primary-100': isDraggable && index !== 0 }"
                    :style="columnStyle(column)"
                >

                    <div class="flex items-center relative">
                        <div
                            class="w-full"
                            :class="{ 'drag-this': index !== 0 }"
                        >

                            <DataNameDisplay
                                class="centered w-full text-smbase px-3 text-primary-800"
                                :dataObj="getOriginalColumn(column.id)"
                            >
                            </DataNameDisplay>

                        </div>

                        <button
                            v-if="isDraggable"
                            type="button"
                            class="cursor-col-resize text-primary-300 absolute -right-1 top-0"
                            @mousedown="startDrag($event, column)"
                        >
                            <i
                                class="fal fa-grip-lines-vertical"
                            >
                            </i>
                        </button>

                        <div
                            v-else
                            class="text-primary-300 ml-1"
                        >
                            <i
                                class="fal fa-pipe"
                            >
                            </i>
                        </div>
                    </div>
                </th>
            </template>
        </Draggable>
    </thead>
</template>

<script>

import Draggable from 'vuedraggable';
import DataNameDisplay from '@/components/customize/DataNameDisplay.vue';

const defaultWidth = 200;
const minWidth = 150;

export default {
    name: 'SpreadsheetHeader',
    components: {
        Draggable,
        DataNameDisplay,
    },
    mixins: [
    ],
    props: {
        isDraggable: Boolean,
        gridMap: {
            type: Array,
            required: true,
        },
        columns: {
            type: Array,
            required: true,
        },
    },
    emits: [
        'update:columns',
    ],
    data() {
        return {
            dragging: null,
            colRefs: {},
            parentRefs: null,
        };
    },
    computed: {
        firstColWidth() {
            const column = this.gridMap?.[0];
            if (!column?.width) {
                const ref = this.colRefs[column?.id];
                return ref ? `${ref.clientWidth}px` : '200px';
            }
            return this.columnWidth(column);
        },
        firstColId() {
            return this.columns.formattedId;
        },
    },
    methods: {
        setRef(dragColumn) {
            return (el) => {
                this.colRefs[dragColumn.id] = el;
            };
        },
        getOriginalColumn(id) {
            return _.find(this.columns, { formattedId: id });
        },
        columnStyle(dragColumn) {
            let columnWidth;
            let columnMinWidth;
            let columnMaxWidth;

            if (_.isObject(dragColumn.width)) {
                const widthObj = dragColumn.width;
                columnWidth = widthObj.width ? `${widthObj.width}px` : 'auto';
                columnMinWidth = `${widthObj.minWidth}px`;
                columnMaxWidth = `${widthObj.maxWidth}px`;
            } else {
                columnWidth = this.getWidth(dragColumn);
                columnMinWidth = columnWidth;
                columnMaxWidth = columnWidth;
            }

            return {
                width: columnWidth,
                minWidth: columnMinWidth,
                maxWidth: columnMaxWidth,
            };
        },
        getWidth(dragColumn) {
            const width = this.columnWidth(dragColumn);
            const val = _.isNumber(width) ? `${width}px` : width;
            return val;
        },
        columnWidth(dragColumn) {
            return dragColumn.width;
        },
        startDrag(event, dragColumn) {
            this.stopDrag();
            this.dragging = {
                columnId: dragColumn.id,
                startX: event.pageX,
                startWidth: dragColumn.width,
            };
            document.addEventListener('mousemove', this.changeWidth);
            document.addEventListener('mouseup', this.stopDrag);
        },
        stopDrag() {
            if (!_.isNull(this.dragging)) {
                this.updateColumns(this.gridMap);
            }
            this.dragging = null;
            document.removeEventListener('mousemove', this.changeWidth);
            document.removeEventListener('mouseup', this.changeWidth);
        },
        changeWidth: _.throttle(function changeWidth(event) {
            if (this.dragging) {
                const dragColumn = _.find(this.gridMap, { id: this.dragging.columnId });
                const difference = event.pageX - this.dragging.startX;
                const newWidth = this.dragging.startWidth + difference;
                const rightMargin = this.colRefs[dragColumn.id].offsetLeft;
                dragColumn.width = (newWidth < minWidth) ? minWidth : newWidth;
                this.dragging.rightMargin = rightMargin + dragColumn.width;
            }
        }, 20),
        updateColumns(newGridMap) {
            const newColumns = newGridMap.map((newCol) => {
                const oldCol = _.find(this.columns, ['formattedId', newCol.id]);
                if (newCol.width !== defaultWidth && newCol.width !== oldCol.width) {
                    return {
                        ...oldCol,
                        width: newCol.width,
                    };
                }
                return oldCol;
            });
            this.$emit('update:columns', newColumns);
        },
    },
    created() {

    },
    mounted() {
        this.parentRefs = this.$parent.$refs;
    },
    beforeUpdate() {
        this.colRefs = {};
    },
};
</script>

<style scoped>

.c-spreadsheet-header {
    &__line {
        width: 2px;
        @apply
            absolute
            h-full
            shadow
            top-0
            z-over
        ;
    }

    &__name {
        @apply
            flex-1
            font-semibold
            px-3.5
            text-primary-800
            text-smbase
        ;
    }

    &__column {
        transition: background-color 0.2s ease-in-out;
        @apply
            pl-2
            pr-2
            py-3
        ;

        &:first-child {
            @apply
                left-0
                rounded-tl-lg
                sticky
                z-over
            ;
        }

        &:last-child {
            @apply
                rounded-tr-lg
            ;
        }
    }
}

</style>
