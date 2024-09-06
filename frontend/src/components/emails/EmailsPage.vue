<template>
    <component
        :is="layoutComponent"
        class="o-links-page"
        v-bind="layoutProps"
    >
        <EmailsLayout
            v-bind="featureMainProps"
            :integrations="integrationsForScope"
            :integrationLists="integrationLists"
            :renewals="renewals"
            :hasIntegrationError="hasIntegrationError"
            :isLoading="isLoading"
            :emailIntegrationsLength="emailIntegrationsLength"
        >
        </EmailsLayout>
    </component>
</template>

<script>

import EmailsLayout from '@/components/emails/EmailsLayout.vue';

import interactsWithFeaturePage from '@/vue-mixins/features/interactsWithFeaturePage.js';
import providesEmailsPageEssentials from '@/vue-mixins/emails/providesEmailsPageEssentials.js';

export default {
    name: 'EmailsPage',
    components: {
        EmailsLayout,
    },
    mixins: [
        interactsWithFeaturePage,
        providesEmailsPageEssentials,
    ],
    props: {

    },
    data() {
        return {
        };
    },
    computed: {
        featureType() {
            return 'EMAILS';
        },
        historyPageType() {
            return '';
        },
        sourceLists() {
            return [];
        },
    },
    methods: {

    },
    created() {
        this.moveItemToListFunction = null;
    },
};
</script>

<style scoped>

/*.o-emails-page {

} */

</style>
