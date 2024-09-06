<template>
    <div class="o-field-sub-fields">
        <h3 class="header-uppercase-light mb-2">
            Sub-fields
        </h3>
        <button
            class="button-rounded--sm button-primary--light"
            :class="{ unclickable: maxSubFields }"
            type="button"
            :disabled="maxSubFields"
            @click="addSubField"
        >
            Add sub-field
        </button>

        <div
            class="relative"
        >
            <div
                v-for="(formField, index) in subFields"
                :key="formField.id"
                class="my-1 rounded-xl p-3 bg-cm-100 relative"
            >
                <FieldSubField
                    :formField="formField"
                    :savedSubfields="savedSubfields"
                    @update:formField="updateFormField(index, $event)"
                >
                </FieldSubField>

                <ClearButton
                    v-if="moreThanOneSubField"
                    positioningClass="absolute top-0 right-0"
                    @click="removeField(index)"
                >
                </ClearButton>
            </div>
        </div>
    </div>
</template>

<script>

import FieldSubField from '@/components/customize/fields/FieldSubField.vue';
import ClearButton from '@/components/buttons/ClearButton.vue';

const subDefault = () => ({
    name: '',
    type: 'LINE',
    val: 'LINE',
    meta: {},
    options: {},
});

export default {
    name: 'FieldSubFields',
    components: {
        FieldSubField,
        ClearButton,
    },
    mixins: [
    ],
    props: {
        subFields: {
            type: Array,
            required: true,
        },
        field: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'update:subFields',
    ],
    data() {
        return {
            newSubField: subDefault(),
        };
    },
    computed: {
        moreThanOneSubField() {
            return this.subFieldsLength > 1;
        },
        addDisabled() {
            return !this.newSubField.name || !this.newSubField.type;
        },
        subFieldsLength() {
            return this.subFields?.length;
        },
        maxSubFields() {
            return this.subFieldsLength === 10;
        },
        savedSubfields() {
            return this.field.options?.fields;
        },
    },
    methods: {
        addSubField() {
            const clone = _.clone(this.subFields);
            clone.push(subDefault());
            this.emitFields(clone);
        },
        removeField(index) {
            const clone = _.clone(this.subFields);
            clone.splice(index, 1);
            this.emitFields(clone);
        },
        updateFormField(index, newVal) {
            const clone = _.clone(this.subFields);
            clone.splice(index, 1, newVal);
            this.emitFields(clone);
        },
        emitFields(fields) {
            this.$emit('update:subFields', fields);
        },
    },
    watch: {
        subFieldsLength: {
            immediate: true,
            handler(newVal) {
                if (!newVal) {
                    this.addSubField();
                }
            },
        },
    },
    created() {

    },
};
</script>

<style scoped>
.o-field-sub-fields {
    &__box {
        @apply
            bg-cm-200
            p-3
        ;
    }

    &__add {
        @apply
            h-8
            text-cm-00
            w-8
        ;
    }

    &__action {
        @apply
            bg-cm-300
            h-4
            text-cm-700
            w-4
        ;

        &:hover {
            @apply
                bg-cm-200
            ;
        }
    }
}
</style>
