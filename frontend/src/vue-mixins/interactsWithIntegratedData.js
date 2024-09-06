import INTEGRATIONS from '@/graphql/account-integrations/AccountIntegrations.gql';
import interactsWithApolloQueries from '@/vue-mixins/interactsWithApolloQueries.js';

export default {
    mixins: [
        interactsWithApolloQueries,
    ],
    apollo: {
        integrations: {
            query: INTEGRATIONS,
            skip() {
                return !!this.shouldSkipIntegrations;
            },
        },
    },
    data() {
        return {
            integrationLists: {},
            renewals: {},
            hasIntegrationError: false,
        };
    },
    computed: {
        integrationsForScope() {
            return this.integrations?.filter((integration) => integration.scopes.includes(this.scope));
        },
        isLoadingInitialIntegrations() {
            return this.$isLoadingQueriesFirstTime(['integrations'])
                || _.some(
                    this.integrations || [],
                    (integration) => this.$apollo.queries[integration.id]?.loading
                        && !this.integrationLists[integration.id]);
        },
    },
    methods: {
        createIntegrationSmartQueries(query, initializeCb, variables = {}, smartQueryOptions = {}) {
            this.integrationsForScope.forEach((integration) => {
                this.$apollo.addSmartQuery(`${integration.id}`, {
                    query,
                    variables: {
                        sourceId: integration.id,
                        ...variables,
                    },
                    manual: true,
                    errorPolicy: 'all',
                    result(results, id) {
                        if (results.data) {
                            this.integrationLists[id] = initializeCb(results.data, this.integrationLists[id]);
                        }
                        if (results.errors) {
                            const redirectError = _.find(results.errors, ['extensions.category', 'redirect']);
                            if (redirectError) {
                                this.renewals[integration.id] = redirectError.extensions.redirect;
                            } else {
                                throw results.error;
                            }
                        } else {
                            this.renewals[integration.id] = null;
                        }
                    },
                    error(error) {
                        // If the error has a redirect then we catch it above,
                        // so we return false here to skip the default error handler.
                        if (error.gqlError?.extensions.category === 'redirect') {
                            return false;
                        }
                        this.hasIntegrationError = true;
                        return true;
                    },
                    ...smartQueryOptions,
                });
            });
        },
    },
};
