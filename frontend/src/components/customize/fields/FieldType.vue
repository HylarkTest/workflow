<template>
    <SettingsHeaderLine class="o-field-type">
        <template
            #header
        >
            {{ header }}
        </template>
        <AllFieldsPicker
            v-if="isNew"
            :fieldVal="fieldType"
            :showFieldConditionFn="showFieldConditionFn"
            bgColor="gray"
            @update:fieldVal="updateFieldType"
        >
        </AllFieldsPicker>

        <div
            v-else
            class="bg-cm-100 rounded-lg px-3 py-1.5 inline-flex"
        >
            {{ fieldName }}
        </div>
    </SettingsHeaderLine>
</template>

<script>

import AllFieldsPicker from '@/components/pickers/AllFieldsPicker.vue';

export default {

    name: 'FieldType',
    components: {
        AllFieldsPicker,
    },
    mixins: [
    ],
    props: {
        header: {
            type: String,
            default: 'Field type',
        },
        fieldType: {
            type: String,
            default: '',
        },
        field: {
            type: Object,
            required: true,
        },
        isNew: Boolean,
        showFieldConditionFn: {
            type: [Function, null],
            default: null,
        },
    },
    emits: [
        'update:fieldType',
    ],
    data() {
        return {
        };
    },
    computed: {
        fieldTypeName() {
            return this.getName(this.fieldType);
        },
        fieldName() {
            if (this.isNew) {
                return '';
            }
            const val = this.field.meta?.display || this.field.type;
            return this.getName(val);
        },
    },
    methods: {
        updateFieldType(field) {
            this.$emit('update:fieldType', field);
        },
        getName(val) {
            return this.$t(`fields.types.${_.camelCase(val)}`);
        },
    },
};
</script>

<!-- <style scoped>
.o-field-type {

}
</style> -->
