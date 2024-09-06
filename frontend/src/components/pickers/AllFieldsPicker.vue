<template>
    <div class="c-all-fields-picker">
        <DropdownInput
            v-model:inputVal="filters.freeText"
            v-blur="selectIfOneResultOrReset"
            class="w-full"
            :modelValue="fullFieldObj"
            :options="filteredFields"
            :allOptions="fieldOptions"
            :displayRule="fieldsDisplay"
            :placeholder="placeholder"
            popupConditionalDirective="show"
            dropdownComponent="DropdownBox"
            :neverHighlighted="true"
            v-bind="$attrs"
            @update:modelValue="selectField"
        >
        </DropdownInput>
        <transition name="t-fade">
            <AlertTooltip
                v-if="error"
            >
                {{ error }}
            </AlertTooltip>
        </transition>
    </div>
</template>

<script>

import filterList from '@/core/filterList.js';
import AlertTooltip from '@/components/popups/AlertTooltip.vue';

import { frontendFields, groupedFields } from '@/core/mappings/frontendFieldTypes.js';

export default {
    name: 'AllFieldsPicker',
    components: {
        AlertTooltip,
    },
    mixins: [
    ],
    props: {
        fieldVal: {
            type: String,
            required: true,
        },
        error: {
            type: String,
            default: '',
        },
        exclude: {
            type: Array,
            default: () => ([]),
        },
        placeholder: {
            type: String,
            default: 'Select a type',
        },
        showFieldConditionFn: {
            type: [Function, null],
            default: null,
        },
    },
    emits: [
        'update:fieldVal',
    ],
    data() {
        return {
            filters: {
                freeText: this.fieldName(this.fieldVal),
            },
        };
    },
    computed: {
        groupedFields() {
            return groupedFields;
        },
        fieldOptions() {
            return frontendFields.filter((field) => {
                const passesCondition = this.showFieldConditionFn
                    ? this.showFieldConditionFn(field)
                    : true;
                return !this.exclude.includes(field.val)
                    && passesCondition;
            });
        },
        fullFieldObj() {
            return _.find(this.fieldOptions, { val: this.fieldVal });
        },
        filteredFields() {
            if (this.filters.freeText === this.fieldName(this.fieldVal)) {
                return [this.fullField];
            }
            return filterList(
                this.fieldOptions,
                this.filters,
                {
                    keys: [{
                        name: 'value',
                        getFn: (field) => {
                            return this.fieldName(field.val);
                        },
                    }],
                }
            );
        },
        fullField() {
            return _.find(this.fieldOptions, { val: this.fieldVal });
        },
    },
    methods: {
        updateInput(field) {
            this.filters.freeText = this.fieldName(field?.val);
        },
        selectField(field) {
            this.updateInput(field);
            this.$emit('update:fieldVal', _.cloneDeep(field));
        },
        selectIfOneResultOrReset() {
            if (this.filteredFields.length === 1) {
                if (this.filteredFields[0].val === this.fieldVal) {
                    return;
                }
                this.selectField(this.filteredFields[0]);
            } else {
                this.selectField(this.fullField);
            }
        },
        fieldName(val) {
            return val ? this.$t(`fields.types.${_.camelCase(val)}`) : null;
        },
    },
    watch: {
        fieldVal() {
            this.filters.freeText = this.fieldName(this.fieldVal);
        },
    },
    created() {
        this.fieldsDisplay = (field) => this.fieldName(field?.val);
    },
};
</script>

<style scoped>

/*.c-all-fields-picker {

} */

</style>
