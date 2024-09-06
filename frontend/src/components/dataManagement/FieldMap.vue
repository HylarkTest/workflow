<template>
    <FieldsMapDisplay
        class="o-field-map"
    >
        <template
            #one
        >
            <label
                v-if="showHeaders"
                class="o-field-map__label"
            >
                Column name
            </label>
            <h4 class="font-semibold text-lg">
                {{ header }}
            </h4>
        </template>

        <template
            #two
        >
            <label
                v-if="showHeaders"
                class="o-field-map__label"
            >
                Extracted data examples
            </label>
            <ul>
                <li
                    v-for="(example, index) in examples"
                    :key="example + index"
                >
                    {{ example }}
                </li>
            </ul>
        </template>

        <template
            #three
        >
            <label
                v-if="showHeaders"
                class="o-field-map__label"
            >
                Hylark field
            </label>
            <div
                v-if="mappingId"
                class="w-full flex flex-col items-center"
            >
                <FieldPicker
                    :modelValue="fieldForColumn"
                    class="w-full"
                    :bgColor="pickerBgColor"
                    :mappingId="mappingId"
                    :showFieldConditionFn="showFieldCondition"
                    :unselectableFieldConditionFn="unselectableFieldCondition"
                    property="id"
                    :showClear="true"
                    @update:modelValue="emitFieldForColumn"
                >
                </FieldPicker>

                <span
                    class="font-bold text-cm-400"
                >
                    or
                </span>

                <button
                    class="button--sm button-primary--medium"
                    type="button"
                    @click="openCreateField"
                >
                    Create a new field
                </button>
            </div>

            <FieldForms
                v-if="isModalOpen"
                :mapping="mapping"
                :showFieldConditionFn="showFieldCondition"
                @closeModal="selectField"
                @newField="newField"
            >
                <template
                    #info
                >
                    <div
                        class="bg-gold-100 p-4 text-sm"
                    >
                        <p>
                            The list of possible field types is restricted
                            to the field types currently accepted by the import tool
                        </p>
                    </div>
                </template>
            </FieldForms>
        </template>
    </FieldsMapDisplay>
</template>

<script>

import FieldsMapDisplay from '@/components/dataManagement/FieldsMapDisplay.vue';
import FieldPicker from '@/components/pickers/FieldPicker.vue';
import FieldForms from '@/components/customize/fields/FieldForms.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

import { arrRemoveId, arrReplaceOrPushId } from '@/core/utils.js';

export default {
    name: 'FieldMap',
    components: {
        FieldPicker,
        FieldsMapDisplay,
        FieldForms,
    },
    mixins: [
        interactsWithModal,
    ],
    props: {
        mapping: {
            type: Object,
            required: true,
        },
        column: {
            type: Object,
            required: true,
        },
        modelValue: {
            type: [Array, null],
            default: null,
        },
        pickerBgColor: {
            type: String,
            default: 'white',
        },
        showHeaders: Boolean,
        importableFieldTypes: {
            type: Array,
            required: true,
        },
    },
    emits: [
        'update:modelValue',
    ],
    data() {
        return {

        };
    },
    computed: {
        mappingId() {
            return this.mapping.id;
        },
        header() {
            return this.column.data[0];
        },
        examples() {
            return this.column.data.slice(1);
        },
        fieldForColumn() {
            return _.find(this.modelValue, { column: this.column.column })?.fieldId || null;
        },
    },
    methods: {
        unselectableFieldCondition(field) {
            // Grays out
            const options = field.options;
            const isList = options.list;
            if (isList || field.id === this.fieldForColumn) {
                return false;
            }
            return !!_.find(this.modelValue, { fieldId: field.id });
        },
        showFieldCondition(field) {
            // Hides it
            if (!this.importableFieldTypes.includes(field.type)) {
                return false;
            }
            const options = field.options;
            return !options.multiSelect && !options.isRange;
        },
        emitFieldForColumn(fieldId) {
            let payload;
            if (!fieldId) {
                payload = arrRemoveId(this.modelValue, this.column.column, 'column');
            } else {
                payload = arrReplaceOrPushId(this.modelValue, this.column.column, {
                    column: this.column.column,
                    fieldId,
                }, 'column');
            }
            return this.$emit('update:modelValue', payload);
        },
        openCreateField() {
            this.openModal();
        },
        selectField() {
            this.closeModal();
        },
        newField(field) {
            this.emitFieldForColumn(field?.id);
        },
    },
    created() {
    },
};
</script>

<style scoped>

.o-field-map {
    &__label {
        @apply
            font-bold
            mb-1
            text-cm-400
            text-sm
        ;
    }
}

</style>
