import { arrRemoveId } from '@/core/utils.js';

export default {
    props: {
        inEditForm: Boolean,
        fieldInfo: {
            type: [Object, null],
            default: null,
        },
    },
    data() {
        return {
            selfEditForm: this.$apolloForm(() => ({
                dataValue: this.fieldInfo?.dataValue || null,
            })),
        };
    },
    computed: {
        modelFieldValue() {
            if (this.useFullForm) {
                return _.get(this, 'dataValue.fieldValue');
            }
            return _.get(this.selfEditForm, this.formPath);
        },
        isLabeled() {
            return this.infoOptions?.labeled;
        },
        infoOptions() {
            return this.dataInfo?.info?.options;
        },
        mainFieldDisplay() {
            return this.mainFieldInfo.displayOption;
        },
        parent() {
            return this.dataInfo.info?.parent;
        },
        mainFieldInfo() {
            return this.parent || this.dataInfo;
        },
        mainIndex() {
            if (_.isNumber(this.parentIndex)) {
                return this.parentIndex;
            }
            if (_.isNumber(this.index)) {
                return this.index;
            }
            return null;
        },
        formPath() {
            let path = 'dataValue.fieldValue';

            const isMainList = this.mainFieldInfo.info?.options?.list;

            // Whether parent or single field, deals with it being a list
            if (isMainList) {
                const mainFieldVal = this.item?.data[this.mainFieldInfo.id];
                let index = this.mainIndex;

                if (this.mainFieldDisplay === 'LIST_FIRST') {
                    index = 0;
                }

                if (this.mainFieldDisplay === 'LIST_MAIN') {
                    index = mainFieldVal?.listValue
                        .findIndex((item) => item.main);
                }

                path = `dataValue.listValue.${index}.fieldValue`;
            }

            if (this.parent) {
                // If there is a parent, the field itself is dealt with here
                const id = this.dataInfo.id;
                let suffix;

                if (this.isList) {
                    // TODO: Revise
                    suffix = `listValue.${this.index}.fieldValue`;
                } else {
                    suffix = 'fieldValue';
                }

                path = `${path}.${id}.${suffix}`;
            }

            return path;
        },
        isList() {
            return this.infoOptions.list;
        },
        useFullForm() {
            // Change due to full list self edit can be
            // done by clicking on the label.
            // This is for easy editing of existing items in the list.
            return this.inEditForm;
        },
    },
    methods: {
        updateDataValue(event) {
            this.$proxyEvent(event, this.modelFieldValue, '', 'update:dataValue');
        },
        saveValue(event) {
            if (this.useFullForm) {
                this.updateDataValue(event);
            } else {
                _.set(this.selfEditForm, this.formPath, event);
                this.updateSelfEditField(this.selfEditForm);
            }
        },
        updateSelfEditField(form) {
            this.$emit('saveField', form);
        },
        getDataVal() {
            if (this.isLabeled) {
                return {
                    label: this.dataValue?.label || '',
                    labelKey: this.dataValue?.labelKey || null,
                    fieldValue: this.dataValue?.fieldValue || null,
                };
            }
            return { fieldValue: this.dataValue?.fieldValue };
        },
        removeOption(val) {
            const formVal = _.get(this.selfEditForm, this.formPath);
            const payload = arrRemoveId(formVal, val.selectValue, 'selectValue');
            this.saveValue(payload);
        },
    },
    watch: {
        dataValue() {
            this.selfEditForm.reset();
        },
    },
};
