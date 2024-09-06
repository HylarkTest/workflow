import { isValueFilled } from '@/core/utils.js';

export default {
    props: {
        dataValue: {
            type: [String, Number, Array, Object, null],
            required: true,
        },
        dataInfo: {
            type: Object,
            required: true,
        },
        item: {
            type: [Object, null],
            default: null,
        },
    },
    data() {
        return {
            processing: false,
        };
    },
    computed: {
        modifiableFieldValue() {
            return _.get(this, this.formPath);
        },
        fieldValue() {
            return this.modifiableFieldValue;
        },
        formPath() {
            return 'dataValue.fieldValue';
        },
        isList() {
            return this.infoOptions?.list;
        },
        isLabeled() {
            return this.infoOptions?.labeled;
        },
        infoOptions() {
            return this.dataInfo?.info?.options;
        },
        processingClass() {
            return { unclickable: this.processing };
        },
    },
    methods: {
        updateDataValue(event, path = '') {
            if (!isValueFilled(event) && !this.modifiableFieldValue) {
                return;
            }
            this.$proxyEvent(event, this.modifiableFieldValue, path, 'update:dataValue');
        },
        updateFromTo(value, key) {
            this.updateDataValue({
                ...this.modifiableFieldValue,
                [key]: value,
            });
        },

    },
};
