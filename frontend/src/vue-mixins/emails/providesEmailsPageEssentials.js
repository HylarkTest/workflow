import fetchesEmailIntegrations from '@/vue-mixins/emails/fetchesEmailIntegrations.js';

import MAILBOXES from '@/graphql/mail/queries/Mailboxes.gql';
import MAILBOXES_WITH_COUNTS from '@/graphql/mail/queries/MailboxesWithCounts.gql';
import { initializeMailboxes } from '@/core/repositories/mailboxRepository.js';

export default {
    mixins: [
        fetchesEmailIntegrations,
    ],
    data() {
        return {
            integrationLists: {},
        };
    },
    computed: {
        sources() {
            return {
                integrations: (this.integrationsForEmails || []).map((integration) => {
                    return {
                        name: integration.accountName,
                        id: integration.id,
                        provider: integration.provider,
                        renewalUrl: this.renewals[integration.id] || null,
                        lists: [...(this.integrationLists[integration.id]?.data || [])],
                    };
                }),
            };
        },
        isLoading() {
            const loadingQueries = this._getQueriesArray([])
                .filter((queryName) => _.endsWith(queryName, '_mailboxes_with_counts'));
            return this.$isLoadingQueries(loadingQueries);
        },
    },
    watch: {
        integrationsForScope() {
            const variables = {};
            if (this.item) {
                variables.forNode = this.item.id;
            }
            this.createIntegrationSmartQueries(
                MAILBOXES,
                initializeMailboxes
            );
            this.createIntegrationSmartQueries(
                MAILBOXES_WITH_COUNTS,
                initializeMailboxes,
                variables,
                {
                    pollInterval: 300_000, // Refresh mailboxes every 5 minutes
                }
            );
        },
    },
    created() {
        this.deleteListFunction = null;
        this.createListFromObjectFunction = null;
        this.updateListFunction = null;
        this.createListFunction = null;
        this.moveListFunction = null;
    },
};
