import { fieldExamples } from '@/core/display/checkUse/fieldExamples.js';

export default {
    methods: {
        resolveFieldsObject(key, defaultNumber) {
            // The output of this function is an object with the field information
            // and the example value

            const keys = key.split('.');
            let field;

            if (keys.length === 1) {
                field = _.find(this.page.fields, ['id', keys[0]]);
            }

            if (!field) {
                if (_.isString(key) && key.includes('{')) {
                    field = {
                        type: 'LINE',
                        name: 'Name',
                    };
                } else {
                    field = _.find(this.page.fields, ['id', keys[0]]);
                }
                if (field.options?.list && (keys[1] === 'count')) {
                    field = {
                        ...field,
                        type: 'NUMBER',
                    };
                } else if (field.type === 'MULTI') {
                    if (_.find(field.options.fields, ['id', keys[1]])) {
                        field = _.find(field.options.fields, ['id', keys[1]]);
                        if (field?.options?.list && (keys[2] === 'count')) {
                            field = {
                                ...field,
                                type: 'NUMBER',
                            };
                        }
                    }
                    if (keys[1] === 'first') {
                        field = _.find(field.options.fields, ['id', keys[2]]);
                    }
                }
            }

            if (!field) {
                return null;
            }
            return {
                ...field,
                value: this.buildFieldDisplay(keys, defaultNumber, field),
            };
        },
        // To make the dataMap work with fields
        buildFieldDisplay(id, defaultNumber, field) {
            if (id && _.isString(id[0]) && id[0].includes('{')) {
                return id[0].replace(/{([^]*?)}/g, (ignore, fieldId) => {
                    const computedField = _.find(this.page.fields, ['id', fieldId]);
                    return this.findDisplay(computedField, defaultNumber);
                });
            }
            return id ? this.getFieldDisplay(id, defaultNumber, field) : null;
        },
        // To get the field displays for the dataMap
        getFieldDisplay(keys, defaultNumber, field, previousField = null) {
            const id = keys[0];
            const remainingKeys = keys.slice(1);
            const fieldObj = field;
            if (remainingKeys.length) {
                return this.getFieldDisplay(remainingKeys, defaultNumber, field, fieldObj);
            }
            if (id === 'count') {
                return fieldExamples[defaultNumber].NUMBER;
            }
            if (id === 'first') {
                return this.findDisplay(previousField, defaultNumber);
            }
            return this.findDisplay(fieldObj, defaultNumber);
        },
        findDisplay(fieldObject, defaultNumber) {
            if (fieldObject?.type === 'IMAGE') {
                const imageType = this.page.type === 'ITEM' ? 'IMAGE_ITEM' : 'IMAGE_PERSON';
                return fieldExamples[defaultNumber][imageType];
            }
            const val = fieldExamples[defaultNumber][fieldObject.type];
            // Where false is an acceptable value
            if (fieldObject?.type === 'BOOLEAN') {
                return val;
            }
            return val || fieldObject.name;
        },
    },
};
