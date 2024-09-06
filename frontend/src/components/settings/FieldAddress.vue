<template>
    <div class="o-field-address">
        <h4 class="settings-form__title">
            Address fields
        </h4>
        <h5 class="font-semibold mb-4 text-sm">
            Select the address lines you wish to include
        </h5>
        <div>
            <CheckHolder
                v-for="(field, key, index) in possibleFields"
                :key="key"
                class="mb-1"
                :disabled="isLastField(field, index)"
                :val="key"
                :value="includedFields"
                @input="emitFields($event, index)"
            >
                {{ field }}
            </CheckHolder>
        </div>
    </div>
</template>

<script>

import { arrRemoveIndex } from '@/core/utils.js';
import formWrapperChild from '@/vue-mixins/formWrapperChild.js';

export default {

    name: 'FieldAddress',
    components: {

    },
    mixins: [
        formWrapperChild,
    ],
    props: {
        possibleFields: {
            type: Object,
            required: true,
        },
        value: {
            type: Array,
            default: null,
        },
    },
    emits: [
        'input',
    ],
    data() {
        return {

        };
    },
    computed: {
        includedFields() {
            return this.formValue.length ? this.formValue : true;
        },
        possibleKeys() {
            return _.keys(this.possibleFields);
        },
    },
    methods: {
        emitFields(event, index) {
            let payload;
            if (_.isBoolean(event)) {
                payload = arrRemoveIndex(this.possibleKeys, index);
            } else if (event.length === this.possibleKeys.length) {
                payload = [];
            } else {
                payload = event;
            }
            this.emitInput(payload);
        },
        isLastField(field, index) {
            const location = this.possibleKeys.indexOf(field);
            return (this.formValue.length === 1) && this.formValue.includes(field) && (location === index);
        },
    },
    created() {

    },
};
</script>

<!-- <style scoped>
.o-field-address {

}
</style>
 -->
