<template>
    <WizardNameDescription
        class="o-page-wizard-name"
        :form="pageForm"
        @pressedEnter="$emit('pressedEnter')"
        @update:form="updateForm"
    >
        <template #nameTitle>
            What is the name of your page?
        </template>

        <template #descriptionTitle>
            Add a description?
        </template>
    </WizardNameDescription>
</template>

<script>

import WizardNameDescription from './WizardNameDescription.vue';

export default {
    name: 'PageWizardName',
    components: {
        WizardNameDescription,
    },
    mixins: [
    ],
    props: {
        pageForm: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'update:pageForm',
        'pressedEnter',
    ],
    data() {
        return {
        };
    },
    computed: {

    },
    methods: {
        updateForm(event) {
            this.$emit('update:pageForm', event);
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-page-wizard-name {

} */

</style>
