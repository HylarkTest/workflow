import { createApolloProvider } from '@vue/apollo-option';
import { createApolloClient } from '@/http/apollo/graphqlClient.js';
import { defaultOptions, defaultApolloClient } from '@/http/apollo/defaultApolloClient.js';
import config from '@/core/config.js';
// import initializeConnections from '@/http/apollo/initializeConnections.js';

// import app from '../app.js';

// Install the vue plugin
// app.use(VueApollo);

// const { createApolloClient, restartWebsockets } = require('vue-cli-plugin-apollo/graphql-client');

// app.config.globalProperties.$filesRoot = filesRoot;

// Call this in the Vue app file
export default function createProvider(options) {
    let client;
    if (options) {
        client = createApolloClient({
            ...defaultOptions,
            ...options,
        });
    } else {
        client = defaultApolloClient();
    }

    // Create vue apollo provider
    return createApolloProvider({
        defaultClient: client,
        clients: {
            default: client, // This is the client for the active base and switches when the base changes
            defaultClient: client, // This is the base agnostic client for global queries
        },
        defaultOptions: {
            $query: {
                notifyOnNetworkStatusChange: true,
                fetchPolicy: 'cache-and-network',
                // update: initializeConnections,
            },
        },
        errorHandler(...args) {
            const error = args[0];
            if (config('debug.logApolloErrors')) {
                // eslint-disable-next-line no-console
                console.log(
                    '%cError',
                    'background: red; color: white; padding: 2px 4px; border-radius: 3px; font-weight: bold;',
                    error.message
                );
            }
            if (error instanceof Error) {
                throw error;
            }
            return Promise.reject(error);
        },
    });
}
