<template>
    <div
        class="c-spreadsheet-cell px-4 py-3 flex items-center w-full justify-center"
        :class="clickableClass"
        :style="widthStyle"
        @click="selectSlot"
    >
        <div
            :class="[mainClass, selectedClass]"
        >
            <slot
                name="cell"
                :dataInfo="dataMap[column.formattedId].dataInfo"
                :item="dataMap[column.formattedId].item"
                v-bind="$attrs"
            >
                <DisplayerContainer
                    :dataInfo="dataMap[column.formattedId].dataInfo"
                    :item="dataMap[column.formattedId].item"
                    :isModifiable="isModifiable"
                    v-bind="$attrs"
                    parentView="SPREADSHEET"
                >
                </DisplayerContainer>
            </slot>
        </div>

        <EntityExtras
            v-if="isSystemName && isModifiable"
            v-bind="extraProps"
            :duplicateItemMethod="duplicateRecord"
            @selectOption="$emit('selectOption', $event)"
        >
        </EntityExtras>

        <div
            v-if="previewMode && isSelected"
            class="c-spreadsheet-cell__highlight"
        >

        </div>

    </div>
</template>

<script>

import { duplicateItem } from '@/core/repositories/itemRepository.js';

export default {
    name: 'SpreadsheetCell',
    components: {

    },
    mixins: [
    ],
    props: {
        index: {
            type: Number,
            required: true,
        },
        cell: {
            type: Object,
            required: true,
        },
        column: {
            type: Object,
            required: true,
        },
        dataMap: {
            type: Object,
            required: true,
        },
        extraProps: {
            type: Object,
            required: true,
        },
        previewMode: Boolean,
        isSelected: Boolean,
        isModifiable: Boolean,
    },
    emits: [
        'selectOption',
        'selectSlot',
    ],
    data() {
        return {

        };
    },
    computed: {
        isSystemName() {
            return this.index === 0;
        },
        mainClass() {
            return this.isSystemName
                ? 'flex-1'
                : 'max-w-full';
        },
        width() {
            return this.cell.width;
        },
        widthStyle() {
            return { maxWidth: `${this.width}px` };
        },
        selectedClass() {
            return this.isSelected ? 'border-2 border-blue-500' : '';
        },
        isClickable() {
            return this.previewMode;
        },
        clickableClass() {
            return { 'cursor-pointer': this.isClickable };
        },
    },
    methods: {
        selectSlot() {
            if (this.isClickable) {
                this.$emit('selectSlot', {
                    dataInfo: this.column,
                    slot: this.cell.id,
                });
            }
        },
        duplicateRecord(records) {
            return duplicateItem(this.extraProps.item, records, this.extraProps.mapping);
        },
    },
    created() {

    },
};
</script>

<style scoped>

.c-spreadsheet-cell {
    &__highlight {
        @apply
            absolute
            border-2
            border-primary-400
            border-solid
            h-full
            right-0
            rounded-lg
            top-0
            w-full
        ;
    }
}

</style>
