<template>
    <FullDialog
        class="o-page-wizard-dialog"
        paddingClass="py-4"
        :confirmClose="true"
        v-bind="$attrs"
        @closeFullDialog="closeFullDialog"
    >
        <template #title>
            <i
                class="far fa-hat-wizard mr-2"
            >
            </i>
            New page wizard
        </template>

        <template #leaveConfirm>
            Your new page was not yet created.
            If you leave this screen, any information you added will not be accessible again.
        </template>

        <PageWizard
            :space="space"
            :potentialPage="potentialPage"
            :initialStep="initialPageWizardStep"
            @closeFullDialog="closeFullDialog"
        >
        </PageWizard>

    </FullDialog>
</template>

<script>

import PageWizard from './PageWizard.vue';
import FullDialog from '@/components/dialogs/FullDialog.vue';

export default {
    name: 'PageWizardDialog',
    components: {
        PageWizard,
        FullDialog,
    },
    mixins: [
    ],
    props: {
        // Not required due to the component being present but not visible at the start
        space: {
            type: [Object, null],
            default: null,
        },
        isActive: Boolean,
        potentialPage: {
            type: [Object, null],
            default: null,
        },
        initialPageWizardStep: {
            type: String,
            default: '',
        },
    },
    emits: [
        'closeFullDialog',
    ],
    data() {
        return {

        };
    },
    computed: {

    },
    methods: {
        closeFullDialog() {
            this.$emit('closeFullDialog');
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-page-wizard-dialog {

} */

</style>
