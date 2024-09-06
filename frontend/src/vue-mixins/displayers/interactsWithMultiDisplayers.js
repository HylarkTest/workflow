import { arrRemove, arrRemoveId } from '@/core/utils.js';

// Pair with interactsWithDisplayersSelfEdit or interactsWithDisplayersEdit
export default {
    computed: {
        fieldOptions() {
            return this.dataInfo.info?.options;
        },
        isMulti() {
            return this.fieldOptions?.multiSelect;
        },
        valueOptions() {
            return this.fieldOptions?.valueOptions || [];
        },
        existingFieldValue() {
            // Defined in the mixins mentioned above
            return this.fieldValue;
        },
        valueOptionsFormatted() {
            return _(this.valueOptions).map((option, key) => {
                return {
                    selectKey: key,
                    selectValue: option,
                };
            }).value();
        },
        dataValueArr() {
            if (!this.existingFieldValue) {
                return [];
            }
            if (!_.isArray(this.existingFieldValue)) {
                return [this.existingFieldValue];
            }
            return this.existingFieldValue;
        },
        selectedValue() {
            return this.existingFieldValue?.selectKey;
        },
        formattedData() {
            return this.existingFieldValue || [];
        },
    },
    methods: {
        removeValue(val, idKey = 'id') {
            let payload;
            if (idKey) {
                payload = arrRemoveId(this.existingFieldValue, val[idKey], idKey);
            } else {
                payload = arrRemove(this.existingFieldValue, val);
            }
            this.updateDataValue(payload);
        },
    },
};
