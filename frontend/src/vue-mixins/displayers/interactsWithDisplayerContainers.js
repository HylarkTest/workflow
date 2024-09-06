import { isBooleanField } from '@/core/display/fullViewFunctions.js';
import { isValueFilled } from '@/core/utils.js';

export default {
    props: {
        showMock: Boolean,
    },
    computed: {
        infoObj() {
            return this.dataInfo?.info;
        },
        infoOptions() {
            return this.infoObj?.options;
        },
        isList() {
            return this.infoOptions?.list;
        },
        dataType() {
            return this.dataInfo.dataType;
        },
        subType() {
            return this.infoObj?.subType;
        },
        isImage() {
            return this.subType === 'IMAGE';
        },
        isLabeled() {
            return this.infoOptions?.labeled;
        },
        displayOption() {
            return this.dataInfo.displayOption;
        },
        meta() {
            return this.infoObj?.meta;
        },
        listDisplay() {
            return this.meta?.listDisplay;
        },
        isNumberedList() {
            return this.listDisplay === 'NUMBERED';
        },
        isListCount() {
            return this.displayOption === 'LIST_COUNT';
        },
        fieldType() {
            return this.infoObj?.fieldType;
        },
        isBooleanField() {
            return isBooleanField(this.fieldType);
        },
        isMultiSelect() {
            return this.infoOptions?.multiSelect;
        },
        defaultItemValue() {
            if (this.isBooleanField) {
                return false;
            }
            if (this.isMultiSelect) {
                return [];
            }
            return null;
        },
    },
    methods: {
        getDataValue(value) {
            const val = value?.fieldValue || value;
            return isValueFilled(val) ? value : null;
        },
    },
};
