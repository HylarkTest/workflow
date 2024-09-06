import {
    getCombo,
    getMockData,
} from '@/core/display/displayerInstructions.js';

import { updateItemProperty } from '@/core/repositories/itemRepository.js';

export default {
    components: {
    },
    mixins: [
    ],
    inheritAttrs: false,
    props: {
        // Information about what is being displayed and how
        dataInfo: {
            type: Object,
            required: true,
        },
        // The data being displayed
        dataValue: {
            type: [String, Object, Number, Boolean, null],
            required: true,
        },
        isModifiable: Boolean,
        showMock: Boolean,
        mapping: {
            type: [Object, null],
            default: null,
        },
        parentView: {
            type: String,
            default: '',
        },
        sizeInstructions: {
            type: String,
            default: '',
        },
        item: {
            type: [Object, null],
            default: null,
        },
        index: {
            type: Number,
            default: null,
        },
        // For multis, where there is a parent
        parentIndex: {
            type: Number,
            default: null,
        },
        prefix: {
            type: String,
            default: null,
        },
    },
    data() {
        return {
            typeKey: '', // Define in component
            showSpecificEdit: false,
        };
    },
    computed: {
        displayFieldValue() {
            return _.get(this, this.formPath);
        },
        formPath() {
            return 'dataValue.fieldValue';
        },
        isList() {
            return this.infoOptions?.list && !this.showMock;
        },
        isLabeled() {
            return this.infoOptions?.labeled;
        },
        isRange() {
            return this.infoOptions?.isRange;
        },
        infoOptions() {
            return this.dataInfo?.info?.options;
        },
        combo() {
            return this.dataInfo?.combo || 1;
        },
        additional() {
            return this.dataInfo?.designAdditional;
        },
        selectedCombo() {
            return getCombo(this.typeKey, this.combo);
        },
        comboObject() {
            return _.isObject(this.selectedCombo);
        },
        displayClasses() {
            if (!this.selectedCombo) {
                return '';
            }
            if (this.comboObject) {
                return this.selectedCombo.classes;
            }
            return this.selectedCombo;
        },
        mockValue() {
            return getMockData(this.typeKey);
        },
        isInSpreadsheet() {
            return this.parentView === 'SPREADSHEET';
        },
        // fieldInfo() {
        //     return {
        //         dataValue: this.dataValue,
        //         dataInfo: this.dataInfo,
        //     };
        // },
        itemId() {
            return this.item?.id;
        },
        fields() {
            return this.mapping?.fields;
        },
        cantModifyClass() {
            return !this.isModifiable ? 'pointer-events-none' : 'cursor-pointer';
        },
    },
    methods: {
        // saveField(form) {
        //     return this.updateDisplayerField(form);
        // },
        addPrefix(id) {
            if (this.prefix) {
                return `${this.prefix}${id}`;
            }
            return id;
        },
        // updateDisplayerField(form) {
        //     return updateItemField(this.mapping, this.itemId, this.addPrefix(this.dataInfo.formattedId), form);
        // },
        updateDisplayerValue(keyInfo, value) {
            return updateItemProperty(this.mapping, this.itemId, keyInfo, value);
        },
        openSpecificEdit() {
            if (this.isModifiable) {
                this.$emit('editField');
            }
        },
    },
};
