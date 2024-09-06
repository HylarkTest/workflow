<template>
    <Modal
        class="o-field-forms"
        containerClass="o-field-forms--width"
        :header="true"
        v-bind="$attrs"
        @closeModal="closeModal"
    >
        <template
            #header
        >
            <div
                class="flex items-center justify-between"
            >
                <h1>
                    {{ whichHeader }}
                </h1>
                <div
                    v-if="fieldType"
                    class="bg-cm-100 px-4 py-1 text-sm rounded-lg"
                >
                    {{ field.name }}
                </div>
            </div>
        </template>

        <slot
            name="info"
        >
        </slot>

        <FieldQuick
            class="p-4"
            :mapping="mapping"
            :field="field"
            :showFieldConditionFn="showFieldConditionFn"
            :showAddAnother="showAddAnother"
            @closeModal="closeModal"
            @newField="newField"
        >
        </FieldQuick>
    </Modal>
</template>

<script>

import FieldQuick from '@/components/customize/fields/FieldQuick.vue';

export default {
    name: 'FieldForms',
    components: {
        FieldQuick,
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
            default: () => ({}),
        },
        hasShowAddAnotherProp: Boolean,
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
            openForm: '',
        };
    },
    computed: {
        whichHeader() {
            if (!this.isNew) {
                return 'Edit field';
            }
            if (this.openForm === 'quick') {
                return 'Add a field';
            }
            return 'Add a field - Advanced';
        },
        isNew() {
            return !this.field.id;
        },
        fieldType() {
            return this.field.type;
        },
        showAddAnother() {
            return this.isNew && this.hasShowAddAnotherProp;
        },
    },
    methods: {
        closeModal() {
            this.$emit('closeModal');
        },
        newField(field) {
            this.$emit('newField', field);
        },
    },
    created() {
        const formState = this.isNew ? 'quick' : 'advanced';
        this.openForm = formState;
    },
};
</script>

<style>
.o-field-forms {
    &--width {
        width: 600px;
    }
}
</style>
