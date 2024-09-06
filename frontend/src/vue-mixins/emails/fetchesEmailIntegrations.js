import interactsWithIntegratedData from '@/vue-mixins/interactsWithIntegratedData.js';

export default {
    mixins: [
        interactsWithIntegratedData,
    ],
    data() {
        return {
            scope: 'EMAILS',
        };
    },
    computed: {
        isLoadingIntegrations() {
            return this.$isLoadingQueries(['integrations']);
        },
        emailIntegrationsLength() {
            return this.integrationsForEmails?.length || 0;
        },
        integrationsForEmails() {
            return this.integrationsForScope;
        },
    },
};
