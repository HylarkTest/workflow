import _ from 'lodash';
import { formKey } from '@/components/inputs/FormWrapper.vue';
import interactsWithFormWrapperValue from '@/vue-mixins/interactsWithFormWrapperValue.js';

export default {
    mixins: [
        interactsWithFormWrapperValue,
    ],
    inject: {
        formObject: {
            from: formKey,
            default: null,
        },
    },
    props: {
        dontScroll: Boolean,
        error: {
            type: String,
            default: '',
        },
        dontUpdateForm: Boolean,
    },
    emits: [
        'update:modelValue',
    ],
    computed: {
        errorMessage() {
            return this.error
                || (this.hasFormAndField && this.formObject.form.errors().getFirst(this.formName));
        },
        shouldScroll() {
            return !this.formObject?.dontScroll && !this.dontScroll;
        },
    },
    methods: {
        emitInput(payload) {
            let parsedPayload = payload;
            if (this.$attrs.type === 'number') {
                const payloadAsNumber = parseInt(payload, 10);
                parsedPayload = _.isNaN(payloadAsNumber) ? undefined : payloadAsNumber;
            } else if (_.isString(payload)) {
                parsedPayload = _.trimStart(payload);
            }
            if (!this.dontUpdateForm && this.hasFormAndField) {
                _.set(this.formObject.form, this.formName, parsedPayload);
                this.formObject.form.errors().clear(this.formName);
            }
            this.$emit(this.inputEvent || 'update:modelValue', parsedPayload);
        },
    },
    mounted() {
        if (this.hasFormAndField && this.shouldScroll) {
            this.formObject.form.addElement(this.formName, this.$el);
        }
    },
    destroy() {
        if (this.hasFormAndField && !this.dontScroll) {
            this.formObject.form.errors().removeElement(this.$el);
        }
    },

};
