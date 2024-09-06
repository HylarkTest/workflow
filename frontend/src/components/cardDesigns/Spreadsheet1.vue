<template>
    <tr
        class="c-spreadsheet1 c-spreadsheet-layout__row"
        :class="mainClass"
    >
        <td
            v-for="(cell, index) in gridMap"
            :key="cell.id"
            class="c-spreadsheet1__cell c-spreadsheet-layout__cell relative align-middle bg-cm-00"
            :style="columnStyle"
        >
            <div class="c-spreadsheet1__content">

                <SpreadsheetCell
                    :mapping="mapping"
                    :index="index"
                    :page="page"
                    :dataMap="dataMap"
                    :cell="cell"
                    :isModifiable="isModifiable"
                    :extraProps="extraProps"
                    :isSummaryView="isSummaryView"
                    :column="getOriginalColumn(cell.id)"
                    :isSelected="isSelectedSlot(cell.id)"
                    :previewMode="previewMode"
                    @selectOption="selectOption"
                    @selectSlot="selectSlot"
                >
                    <template
                        #cell="scope"
                    >
                        <!-- Data info and item are passed to the slot rather
                        than in scope due to their importance and for
                        prop validation -->
                        <slot
                            name="cell"
                            :dataInfo="scope.dataInfo"
                            :item="scope.item"
                            v-bind="scope"
                        >
                        </slot>
                    </template>
                </SpreadsheetCell>
            </div>
        </td>
    </tr>
</template>

<script>

import interactsWithCardDesigns from '@/vue-mixins/style/interactsWithCardDesigns.js';

export default {
    name: 'Spreadsheet1',
    components: {

    },
    mixins: [
        interactsWithCardDesigns,
    ],
    inheritAttrs: false,
    props: {
        // item: {
        //     type: Object,
        //     required: true,
        // },
        gridMap: {
            type: Array,
            required: true,
        },
        columns: {
            type: Array,
            required: true,
        },
        isSummaryView: Boolean,
    },
    data() {
        return {
            defaultSlotNames: true,
        };
    },
    computed: {
    },
    methods: {
        getWidth(dragColumn) {
            return `${this.columnWidth(dragColumn)}px`;
        },
        columnWidth(dragColumn) {
            return dragColumn.width;
        },
        columnStyle(dragColumn) {
            const width = this.getWidth(dragColumn);
            return {
                width,
                minWidth: width,
                maxWidth: width,
            };
        },
        getOriginalColumn(id) {
            return _.find(this.columns, { formattedId: id });
        },
    },
    created() {

    },
};
</script>

<style scoped>

.c-spreadsheet1 {
    &__content {
        @apply
            flex
            h-full
            items-center
        ;
    }

    &__cell {
        &:first-child {
            @apply
                left-0
                sticky
                z-over
            ;
        }
    }
}

</style>
