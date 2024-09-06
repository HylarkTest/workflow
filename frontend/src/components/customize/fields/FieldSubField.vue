<template>
    <div class="o-field-sub-field">
        <div class="flex items-center">
            <InputBox
                ref="name"
                :modelValue="formField.name"
                size="sm"
                data-form-type="other"
                maxlength="60"
                placeholder="Sub-field name"
                @update:modelValue="updateField('name', $event)"
            >
            </InputBox>
            <div
                class="flex-1 pl-2"
            >
                <AllFieldsPicker
                    v-if="isNew"
                    :fieldVal="formField.val"
                    size="sm"
                    :exclude="['MULTI']"
                    @update:fieldVal="updateFieldVal"
                >
                </AllFieldsPicker>

                <div
                    v-else
                    class="text-xssm py-1.5 px-3"
                >
                    {{ fieldName }}
                </div>
            </div>
        </div>

        <FieldExtras
            :subField="true"
            :category="formField.options.category"
            :multiSelect="formField.options.multiSelect"
            :fixedCurrency="formField.options.currency"
            :symbol="symbol"
            :form="formField"
            :field="savedSubfield"
            :isNew="isNew"
            :valueOptions="formField.options.valueOptions"
            currentBg="gray"
            @update:multiSelect="updateField('options.multiSelect', $event)"
            @update:icon="updateField('meta.symbol', $event)"
            @update:category="updateField('options.category', $event)"
            @update:valueOptions="updateField('options.valueOptions', $event)"
            @update:fixedCurrency="updateField('options.currency', $event)"
        >
        </FieldExtras>

        <div
            class="mt-4"
        >
            <ButtonEl
                class="flex items-center text-xssm"
                @click="toggleMore"
            >
                <div
                    class="button-primary circle-center h-3.5 w-3.5 mr-2"
                >
                    <i
                        class="fas"
                        :class="showMore ? 'fa-angle-up' : 'fa-angle-down'"
                    >
                    </i>
                </div>
                <span
                    class="font-semibold"
                >
                    More options
                </span>
            </ButtonEl>
            <FieldOptions
                v-if="showMore"
                class="mt-3"
                :meta="meta"
                :options="formField.options"
                :field="savedSubfield"
                :isNew="isNew"
                headerClass="header-uppercase-light"
                @update:options="updateField('options', $event)"
                @update:meta="updateField('meta', $event)"
            >
            </FieldOptions>
        </div>
    </div>
</template>

<script>

import AllFieldsPicker from '@/components/pickers/AllFieldsPicker.vue';
import FieldExtras from '@/components/customize/fields/FieldExtras.vue';
import FieldOptions from '@/components/customize/fields/FieldOptions.vue';

export default {
    name: 'FieldSubField',
    components: {
        AllFieldsPicker,
        FieldExtras,
        FieldOptions,
    },
    mixins: [
    ],
    props: {
        formField: {
            type: Object,
            required: true,
        },
        // field: {
        //     type: Object,
        //     required: true,
        // },
        savedSubfields: {
            type: [Array, null],
            default: null,
        },
    },
    emits: [
        'update:formField',
        'update:meta',
    ],
    data() {
        return {
            showMore: false,
        };
    },
    computed: {
        isNew() {
            return _.isEmpty(this.savedSubfield);
        },
        symbol() {
            return this.formField.meta?.symbol || '';
        },
        meta() {
            return this.formField.meta || {};
        },
        fieldName() {
            if (this.isNew) {
                return '';
            }
            const val = this.formField.val || this.formField.meta?.display || this.formField.type;
            return this.getName(val);
        },
        savedSubfield() {
            return this.savedSubfields?.find((field) => {
                return field.id === this.formField.id;
            }) || {};
        },
    },
    methods: {
        updateField(valKey, val) {
            const clone = _.clone(this.formField);
            _.set(clone, valKey, val);
            this.$emit('update:formField', clone);
        },
        updateFieldVal(obj) {
            const clone = _.clone(this.formField);
            clone.type = obj.type;
            clone.val = obj.val;
            clone.meta = obj.meta;
            clone.options = obj.options;
            this.$emit('update:formField', clone);
        },
        toggleMore() {
            this.showMore = !this.showMore;
        },
        getName(val) {
            return this.$t(`fields.types.${_.camelCase(val)}`);
        },
    },
    created() {

    },
};
</script>

<!-- <style scoped>
.o-field-sub-field {

}
</style> -->
