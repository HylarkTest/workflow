<template>
    <WizardNameDescription
        class="o-page-wizard-details"
        :form="blueprintForm"
        :showSingularName="true"
        @update:form="updateForm"
    >
        <template #nameTitle>
            What is the name of your blueprint?
        </template>

        <template #descriptionTitle>
            Blueprint description
        </template>
    </WizardNameDescription>
</template>

<script>

import WizardNameDescription from './WizardNameDescription.vue';

export default {
    name: 'PageWizardDetails',
    components: {
        WizardNameDescription,
    },
    mixins: [
    ],
    props: {
        blueprintForm: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'update:blueprintForm',
    ],
    data() {
        return {

        };
    },
    computed: {

    },
    methods: {
        updateForm(event) {
            this.$emit('update:blueprintForm', event);
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-page-wizard-details {

} */

</style>
