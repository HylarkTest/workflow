<template>
    <div class="c-form-fields">
        <div
            v-for="field in formattedFields"
            :key="field.formattedId"
            class="mb-6"
        >
            <FormFieldItem
                :field="field"
                :form="form"
                :errors="errors"
                :prepopulatedFields="prepopulatedFields"
                @update:dataValue="updateDataValue"
            >
            </FormFieldItem>
        </div>
    </div>
</template>

<script>

export default {
    name: 'FormFields',
    components: {

    },
    mixins: [
    ],
    props: {
        formattedFields: {
            type: Array,
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
        'update:form',
    ],
    data() {
        return {
        };
    },
    computed: {
    },
    methods: {
        updateDataValue(event, fieldId) {
            this.$proxyEvent(event, this.form, fieldId, 'update:form');
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.c-form-fields {

} */

</style>
