import _ from 'lodash';
import { formKey } from '@/components/inputs/FormWrapper.vue';

export default {
    inject: {
        formObject: {
            from: formKey,
            default: null,
        },
    },
    computed: {
        formValue() {
            if (_.has(this.$options.props, 'modelValue') || _.has(this.$attrs, 'modelValue')) {
                return _.has(this.$options.props, 'modelValue') ? this.modelValue : this.$attrs.modelValue;
            }
            if (this.hasFormAndField) {
                return _.get(this.formObject.form, this.formName.split('.'));
            }
            return undefined;
        },
        hasFormValue() {
            return !!this.formValue || _.isBoolean(this.formValue);
        },
        hasFormAndField() {
            return this.formObject && this.formName && _.has(this.formObject.form, this.formName);
        },
        formName() {
            return _.has(this.$options.props, 'formField') ? this.formField : this.$attrs.formField;
        },
    },
};
