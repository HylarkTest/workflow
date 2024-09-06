<template>
    <div
        class="c-spreadsheet-container relative"
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

        <div class="overflow-x-auto relative">
            <div
                v-if="dragging && dragging.rightMargin"
                class="c-spreadsheet-container__line bg-primary-400"
                :style="{ left: dragging.rightMargin + 'px' }"
            >

            </div>

            <table class="table-reset">
                <thead class="">
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
                                class="c-spreadsheet-container__column bg-primary-200"
                                :class="{ 'hover:bg-primary-100': isDraggable && index !== 0 }"
                                :style="columnStyle(column)"
                            >

                                <div class="flex justify-center items-center relative">
                                    <div
                                        class="c-spreadsheet-container__name u-ellipsis"
                                        :class="{ 'drag-this': index !== 0 }"
                                    >
                                        {{ column.name }}
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
                <tbody>
                    <Spreadsheet1
                        v-for="item in items"
                        :key="item.id"
                        :item="item"
                        :gridMap="gridMap"
                        :columns="columns"
                        :page="page"
                        :dataStructure="validData"
                        :dataValueObject="item"
                        :mapping="mapping"
                        class="c-spreadsheet-container__row"
                    >
                    </Spreadsheet1>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>

import Draggable from 'vuedraggable';

const defaultWidth = 200;
const minWidth = 130;

export default {
    name: 'SpreadsheetContainer',
    components: {
        Draggable,
    },
    mixins: [
    ],
    props: {
        columns: {
            type: Array,
            required: true,
        },
        items: {
            type: Array,
            required: true,
        },
        isDraggable: Boolean,
        page: {
            type: [Object, null],
            default: null,
        },
        mapping: {
            type: Object,
            required: true,
        },
        validData: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'update:columns',
    ],
    data() {
        return {
            gridOverwrite: null,
            gridMap: this.getDragColumnMap(),
            dragging: null,
            colRefs: {},
        };
    },
    computed: {
        firstColId() {
            return this.columns.formattedId;
        },
        firstColWidth() {
            const column = this.gridMap[0];
            if (!column.width) {
                const ref = this.colRefs[column.id];
                return ref ? `${ref.clientWidth}px` : '200px';
            }
            return this.columnWidth(column);
        },
    },
    methods: {
        columnStyle(dragColumn) {
            const width = this.getWidth(dragColumn);
            return {
                width,
                minWidth: width,
                maxWidth: width,
            };
        },
        setRef(dragColumn) {
            return (el) => {
                this.colRefs[dragColumn.id] = el;
            };
        },
        getWidth(dragColumn) {
            return `${this.columnWidth(dragColumn)}px`;
        },
        columnWidth(dragColumn) {
            return dragColumn.width;
        },
        getDragColumnMap() {
            return this.columns.map((column, index) => {
                let width;
                if (column.width) {
                    width = column.width;
                } else if (index === 0) {
                    width = 300;
                } else {
                    width = defaultWidth;
                }
                return {
                    id: column.formattedId,
                    width,
                    name: this.columnName(column),
                };
            });
        },
        columnName(column) {
            const name = column.name;
            if (name) {
                return name;
            }

            return this.$t('common.noName');
        },
        getOriginalColumn(id) {
            return _.find(this.columns, { formattedId: id });
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
            this.gridMap = newGridMap;
            const newColumns = this.gridMap.map((newCol) => {
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
    watch: {
        columns() {
            this.gridMap = this.getDragColumnMap();
        },
    },
    created() {
    },
    beforeUpdate() {
        this.colRefs = {};
    },
};
</script>

<style>

/*.cursor-test {
     stylelint-disable-next-line
    cursor: col-resize;
}*/

.c-spreadsheet-container {
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

    &__column {
        transition: background-color 0.2s ease-in-out;
        @apply
            pl-4
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

            .c-spreadsheet-container__shadow {
                @apply
                    block
                ;
            }
        }

        &:last-child {
            @apply
                rounded-tr-lg
            ;
        }
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

    &__row {
        &:nth-child(odd) {
            .c-spreadsheet-container__cell {
                @apply
                    bg-cm-00
                ;
            }
        }

        &:nth-child(even) {
            @apply
                bg-primary-50
            ;

            .c-spreadsheet-container__cell {
                @apply
                    bg-primary-50
                ;
            }
        }
    }

    &__cell {
        &:not(:first-child) {
            .c-spreadsheet-container__content {
                @apply
                    justify-center
                ;
            }
        }

        &:first-child {
            @apply
                left-0
                sticky
                z-over
            ;

            .c-spreadsheet-container__shadow {
                @apply
                    block
                ;
            }

            /*.c-spreadsheet-container__content {
                @apply
                ;
            }*/
        }
    }

    &__shadow {
        @apply
            absolute
            bg-primary-200
            h-full
            hidden
            right-0
            shadow-center-darker
            top-0
            w-px
        ;
    }
}

</style>
