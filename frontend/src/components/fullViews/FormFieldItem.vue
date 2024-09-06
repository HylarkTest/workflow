<template>
    <div class="o-form-field-item">
        <div
            class="flex items-baseline"
        >
            <i
                v-if="isList"
                class="fa-regular fa-bars mr-2"
            >
            </i>
            <span class="label-data mb-1">
                {{ field.name }}
            </span>
            <span
                v-if="isPrepopulated"
                v-t="'labels.prepopulated'"
                class="text-xs ml-2 rounded-full bg-secondary-200 px-2 font-semibold text-secondary-600"
            >
            </span>
        </div>

        <DisplayerEditContainer
            :dataValue="fieldDataValue"
            :dataInfo="field"
            :inEditForm="true"
            :isModifiable="true"
            :formField="`data.${field.id}`"
            :fullForm="form"
            :error="errors.getFirst(`data.${field.id}.*`)"
            @update:dataValue="updateDataValue"
        >
        </DisplayerEditContainer>
    </div>
</template>

<script>

export default {
    name: 'FormFieldItem',
    components: {
    },
    mixins: [
    ],
    props: {
        field: {
            type: Object,
            required: true,
        },
        form: {
            type: Object,
            required: true,
        },
        errors: {
            type: Object,
            default() {
                return {};
            },
        },
        prepopulatedFields: {
            type: [Array, null],
            default: null,
        },
    },
    emits: [
        'update:dataValue',
    ],
    data() {
        return {

        };
    },
    computed: {
        isList() {
            return this.field.info?.options?.list;
        },
        isPrepopulated() {
            return _.find(this.prepopulatedFields, { id: this.field.id }) && this.form[this.field.id];
        },
        fieldDataValue() {
            return this.form[this.field.id] || null;
        },
    },
    methods: {
        updateDataValue(event) {
            this.$emit('update:dataValue', event, this.field.id);
        },
    },
    created() {
    },
};
</script>

<style scoped>
/* .o-form-field-item {

} */
</style>
