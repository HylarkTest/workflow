<template>
    <FormWrapper
        class="o-field-quick"
        :form="form"
    >
        <FieldName
            ref="name"
            class="mb-8"
        >
        </FieldName>

        <div class="mb-8 relative">
            <FieldType
                :isNew="isNew"
                :field="field"
                :fieldType="form.val"
                :showFieldConditionFn="showFieldConditionFn"
                @update:fieldType="updateField"
            >
            </FieldType>

            <div
                class="relative"
            >
                <FieldSubFields
                    v-if="form.type === 'MULTI' || field.type === 'MULTI'"
                    v-model:subFields="form.options.fields"
                    class="mt-4"
                    :field="field"
                >
                </FieldSubFields>
                <AlertTooltip
                    v-if="hasSubFieldError"
                >
                    {{ form.errors().getFirst('options.fields.*') }}
                </AlertTooltip>
            </div>

            <FieldExtras
                v-model:category="form.options.category"
                v-model:multiSelect="form.options.multiSelect"
                v-model:fixedCurrency="form.options.currency"
                v-model:symbol="symbol"
                v-model:valueOptions="form.options.valueOptions"
                :primary="!!form.options.primary"
                :form="form"
                :field="field"
                :hasPrimaryImage="hasPrimaryImage"
                :isNew="isNew"
                @update:primary="form.options.primary = $event"
            >
            </FieldExtras>
        </div>

        <FieldSection
            v-model:section="formSection"
            class="mb-8"
            :mapping="mapping"
        >
        </FieldSection>

        <FieldMore
            v-model:options="form.options"
            v-model:meta="form.meta"
            class="mb-4"
            :field="field"
            :isNew="isNew"
        >
        </FieldMore>

        <SaveOptions
            :buttons="saveButtons"
            :disabled="processing"
            :error="buttonError"
            @submit="goSubmit"
        >
        </SaveOptions>
    </FormWrapper>
</template>

<script>
import FieldName from '@/components/customize/fields/FieldName.vue';
import FieldType from '@/components/customize/fields/FieldType.vue';
import FieldSection from '@/components/customize/fields/FieldSection.vue';
import FieldExtras from '@/components/customize/fields/FieldExtras.vue';
import FieldSubFields from '@/components/customize/fields/FieldSubFields.vue';
import SaveOptions from '@/components/buttons/SaveOptions.vue';
import FieldMore from '@/components/customize/fields/FieldMore.vue';
import { createMappingField, updateMappingField } from '@/core/repositories/mappingRepository.js';
import AlertTooltip from '@/components/popups/AlertTooltip.vue';

export default {

    name: 'FieldQuick',
    components: {
        AlertTooltip,
        FieldName,
        FieldType,
        FieldMore,
        FieldExtras,
        FieldSection,
        FieldSubFields,
        SaveOptions,
    },
    mixins: [
    ],
    props: {
        mapping: {
            type: Object,
            required: true,
        },
        field: {
            type: Object,
            required: true,
        },
        showAddAnother: Boolean,
        showFieldConditionFn: {
            type: [Function, null],
            default: null,
        },
    },
    emits: [
        'closeModal',
        'newField',
    ],
    data() {
        return {
            processing: false,
            form: this.$apolloForm(() => {
                const data = {
                    mappingId: this.mapping.id,
                    name: this.field.name || '',
                    section: this.field.section || null,
                    meta: this.field.meta || {},
                    options: this.field.options || {},
                };

                if (!this.field.id) {
                    data.type = 'LINE';
                    data.val = 'LINE';
                } else {
                    data.id = this.field.id;
                }
                return data;
            }),
        };
    },
    computed: {
        buttonError() {
            return this.labelOptionsError;
        },
        labelOptionsError() {
            return this.form.errors().getFirst('options.labeled.labels*');
        },
        formSection: {
            get() {
                return _.find(this.mapping.sections, ['id', this.form.section]);
            },
            set(section) {
                this.form.section = section?.id;
            },
        },
        symbol: {
            get() {
                return this.form.meta?.symbol || '';
            },
            set(val) {
                this.form.meta.symbol = val;
            },
        },
        formType() {
            return this.form.type;
        },
        formList() {
            return this.form.options?.list;
        },
        isNew() {
            return !this.field.id;
        },
        allFields() {
            return this.mapping.fields;
        },
        imageFields() {
            return _.filter(this.allFields, { type: 'IMAGE' });
        },
        isImagePrimary() {
            return this.field.options?.primary;
        },
        hasPrimaryImage() {
            return _.some(this.imageFields, 'options.primary') && !this.isImagePrimary;
        },
        hasSubFieldError() {
            return this.form.errors().getFirst('options.fields.*');
        },
        saveButtons() {
            return this.isNew && this.showAddAnother
                ? ['another']
                : [];
        },
    },
    methods: {
        updateField(field) {
            this.form.type = field.type;
            this.form.val = field.val;
            this.form.meta = field.meta || null;
            this.form.options = field.options;
        },
        goSubmit(action) {
            if (action === 'close') {
                this.submitAndClose();
            } else {
                this.submitAndClear();
            }
        },
        async submitAndClose() {
            this.form.setOptions({ clear: false });
            await this.submitForm();
            this.$emit('closeModal');
        },
        async submitAndClear() {
            this.form.setOptions({ clear: true });
            await this.submitForm();
            this.$nextTick(() => {
                this.form.type = 'LINE';
                this.form.val = 'LINE';
            });
        },
        async submitForm() {
            this.processing = true;
            try {
                if (this.isNew) {
                    const data = await createMappingField(this.form);
                    const fields = data.data.createMappingField?.mapping.fields;
                    const newField = fields?.[fields.length - 1];
                    this.$emit('newField', newField);
                } else {
                    await updateMappingField(this.form);
                }
            } finally {
                this.processing = false;
            }
        },
        changeIcon() {

        },
    },
    watch: {
        formType(newVal) {
            if (newVal === 'IMAGE' && !this.hasPrimaryImage) {
                this.form.options.primary = true;
            }
        },
        formList(newVal) {
            if (this.formType === 'IMAGE' && newVal) {
                this.form.options.primary = false;
            }
        },
    },
    created() {
    },
    mounted() {
        if (this.isNew) {
            this.$refs.name.$refs.name.focus();
        }
    },
};
</script>

<style scoped>
.o-field-quick {
    &__section {
        @apply
            flex
            mb-20
        ;
    }

    &__header {
        @apply
            font-semibold
            text-sm
            w-40
        ;
    }

    &__alt {
        @apply
            border
            border-primary-600
            border-solid
            text-primary-600
        ;
    }

    &__off {
        @apply
            bg-cm-100
        ;

        &:hover {
            @apply
                bg-cm-200
            ;
        }
    }
}
</style>
