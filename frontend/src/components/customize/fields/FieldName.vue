<template>
    <SettingsHeaderLine class="o-field-name">
        <template
            #header
        >
            {{ header }}
        </template>

        <InputBox
            ref="name"
            :formField="formKey"
            name="fieldName"
            bgColor="gray"
            data-form-type="other"
            maxlength="60"
            placeholder="Your field's name"
        >
        </InputBox>
    </SettingsHeaderLine>
</template>

<script>

export default {
    name: 'FieldName',
    components: {

    },
    mixins: [
    ],
    props: {
        formKey: {
            type: String,
            default: 'name',
        },
        header: {
            type: String,
            default: 'Field name',
        },
    },
    data() {
        return {

        };
    },
    computed: {

    },
    methods: {

    },
    created() {

    },
};
</script>

<!-- <style scoped>
.o-field-name {

}
</style> -->
