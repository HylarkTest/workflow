<template>
    <FullDialog
        class="o-new-base-page"
        paddingClass="py-4"
        :confirmClose="true"
        @closeFullDialog="closeFullDialog"
    >
        <template #title>
            <i
                class="far fa-hat-wizard mr-2"
            >
            </i>
            New base wizard
        </template>

        <template #leaveConfirm>
            Your new base has not yet been created.
            If you leave this screen, any information you added will not be accessible again.
        </template>

        <BaseWizard
            @closeFullDialog="closeFullDialog"
        >
        </BaseWizard>

    </FullDialog>
</template>

<script>

import BaseWizard from './BaseWizard.vue';
import FullDialog from '@/components/dialogs/FullDialog.vue';

export default {
    name: 'NewBasePage',
    components: {
        BaseWizard,
        FullDialog,
    },
    mixins: [
    ],
    props: {

    },
    data() {
        return {

        };
    },
    computed: {

    },
    methods: {
        closeFullDialog() {
            this.$router.push({ name: 'home' });
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-new-base-page {

} */

</style>
