import { getFormattedMockData } from '@/core/display/displayerInstructions.js';
import { deleteItem, duplicateItem } from '@/core/repositories/itemRepository.js';

export default {
    components: {
    },
    props: {
        dataValueObject: {
            type: (Object, null),
            default: null,
            // THE DATA VALUES (of the item)
        },
        dataStructure: {
            type: [Array, null],
            default: null,
            // DATA MAP WITH SLOTS (the flat and formatted)
        },
        // Non-clickable, no data, always shows gray slots
        blank: Boolean,

        // Slots clickable, can have data, shows gray slot if no data
        selectorMode: Boolean,

        // Slots not clickable, only show if there is data
        previewMode: Boolean,

        selectedSlot: {
            type: [String, Array],
            default: '',
        },
        page: {
            type: [Object, null],
            default: null,
        },
        mapping: {
            type: [Object, null],
            default: null,
        },
        isReadOnly: Boolean,
    },
    data() {
        return {
            deleteProcessing: false,
            defaultSlotNames: false,
        };
    },
    computed: {
        isModifiable() {
            return !this.blank
                && !this.selectorMode
                && !this.previewMode
                && !this.isReadOnly;
        },
        dataMap() {
            if (!this.dataStructure) {
                return {};
            }
            return _(this.dataStructure).map((item) => {
                let slot = item.slot;
                if (this.defaultSlotNames && !slot) {
                    slot = item.formattedId;
                }
                return [
                    slot,
                    {
                        dataInfo: item,
                        item: this.getDataItem(item),
                    },
                ];
            }).fromPairs().value();
        },
        showMock() {
            return this.selectorMode || this.previewMode;
        },
        slotItemProps() {
            return {
                isModifiable: this.isModifiable,
                blank: this.blank,
                selectorMode: this.selectorMode,
                mapping: this.mapping,
                page: this.page,
                previewMode: this.previewMode,
                ...this.$attrs,
            };
        },
        extraProps() {
            return {
                page: this.page,
                cantModify: !this.isModifiable,
                mapping: this.mapping,
                item: this.dataValueObject,
            };
        },
        mainClass() {
            return this.deleteProcessingClass;
        },
        deleteProcessingClass() {
            return { unclickable: this.deleteProcessing };
        },
    },
    methods: {
        hideSlot(slotKey) {
            return !this.dataMap[slotKey] && !(this.blank || this.selectorMode);
        },
        selectSlot(event, slotName) {
            let args;
            if (event.slot) {
                args = event;
            } else {
                args = { ...event, slot: slotName };
            }
            this.$emit('selectSlot', args);
        },
        isSelectedSlot(slotName) {
            return this.selectedSlot === slotName;
        },
        getDataItem(item) {
            if (this.showMock) {
                return getFormattedMockData(item);
            }
            return this.dataValueObject;
        },
        async selectOption(option) {
            if (option === 'DELETE') {
                this.deleteProcessing = true;
                try {
                    await deleteItem(this.dataValueObject, this.mapping);
                } catch (error) {
                    this.deleteProcessing = false;
                    throw error;
                }
            }
        },
        duplicateRecord(records) {
            return duplicateItem(this.slotItemProps.item, records, this.mapping);
        },
    },
};
