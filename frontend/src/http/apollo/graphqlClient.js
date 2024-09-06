/* eslint-disable no-param-reassign */

import RELEASE from 'RELEASE';
import { setContext } from '@apollo/client/link/context';
import { getMainDefinition, isMutationOperation } from '@apollo/client/utilities';
import { createPersistedQueryLink } from '@apollo/client/link/persisted-queries';
import {
    InMemoryCache, ApolloClient, split, from, ApolloLink, createHttpLink,
} from '@apollo/client/core';
import { createUploadLink } from 'apollo-upload-client';
import axios from 'axios';
import { throttle } from 'lodash';
import { SentryLink } from 'apollo-link-sentry';
import { isProduction, obfuscate, report } from '@/core/utils.js';
import config from '@/core/config.js';
import PusherLink from '@/http/apollo/createLighthouseSubscriptionLink.js';

import pusher from '@/core/pusher/main.js';

let initializePromise;
let lastInitializedAt;
const initializeInterval = (config('session.lifetime') / 2) * 60 * 1000;
export function initializeCSRF(force = false) {
    if (!initializePromise) {
        if (!force
            && lastInitializedAt
            && (new Date()).getTime() - lastInitializedAt < initializeInterval) {
            return Promise.resolve();
        }
        initializePromise = _.retry([0, 100, 200], () => axios.get('/csrf-cookie'))
            .then(() => {
                lastInitializedAt = (new Date()).getTime();
            })
            .finally(() => {
                initializePromise = null;
            });
    }
    return initializePromise;
}
initializeCSRF.flush = () => {
    lastInitializedAt = null;
};

/**
 # The names of all the asset files change after each deployment.
 # This is to bust any browser caching.
 # However this has the problem of causing errors for people using the
 # site during a deployment, because their browser will then be trying
 # to access assets that no longer exist.
 # This usually happens when switching pages.
 # We can solve this by making public an identifier for the latest
 # release (in this case, a timestamp).
 # The browser can periodically check if the release in the code
 # matches the public release file. If not then it triggers a refresh
 # on the next page switch. To the user it just looks like there was a
 # slighlty longer page load time.
 */
export const newReleaseAvailable = throttle(
    async () => {
        if (!isProduction || !RELEASE) {
            return false;
        }
        const { data } = await axios.get(`/latest_release.txt?tz=${(new Date()).getTime()}`);

        const latestRelease = data.toString().trim();

        return !latestRelease.startsWith('<') && latestRelease !== RELEASE;
    },
    10_000
);

async function defaultGetAuth(tokenName) {
    // await initializeCSRF();
    if (typeof window !== 'undefined') {
        // get the authentication token from local storage if it exists
        const token = window.localStorage.getItem(tokenName);
        // return the headers to the context so httpLink can read them
        return token ? `Bearer ${token}` : '';
    }
    return null;
}

export function createApolloClient({
    // Client ID if using multiple Clients
    clientId = 'defaultClient',
    // URL to the HTTP API
    httpEndpoint,
    // Url to the Websocket API
    // wsEndpoint = null,
    // Token used in localstorage
    tokenName = 'apollo-token',
    // Enable this if you use Query persisting with Apollo Engine
    persisting = false,
    // Is currently Server-Side Rendering or not
    ssr = false,
    // Only use Websocket for all requests (including queries and mutations)
    // websocketsOnly = false,
    // Custom starting link.
    // If you want to replace the default HttpLink, set `defaultHttpLink` to false
    link = null,
    // Custom pre-auth links
    // Useful if you want, for example, to set a custom middleware for refreshing an access token.
    preAuthLinks = [],
    // If true, add the default HttpLink.
    // Disable it if you want to replace it with a terminating link using `link` option.
    defaultHttpLink = true,
    // Options for the default HttpLink
    httpLinkOptions = {},
    // Custom Apollo cache implementation (default is apollo-cache-inmemory)
    cache = null,
    // Options for the default cache
    inMemoryCacheOptions = {},
    // Additional Apollo client options
    apollo = {},
    // Function returning Authorization header token
    getAuth = defaultGetAuth,
    // Local Schema
    typeDefs = undefined,
    // Local Resolvers
    resolvers = undefined,
    // Hook called when you should write local state in the cache
    onCacheInit = undefined,
}) {
    let wsClient; let stateLink;
    // const disableHttp = websocketsOnly && !ssr && wsEndpoint;

    // Apollo cache
    if (!cache) {
        cache = new InMemoryCache(inMemoryCacheOptions);
    }

    const uploadLink = createUploadLink({
        uri: httpEndpoint,
        ...httpLinkOptions,
    });

    // It doesn't work :(
    // const deferAdapterLink = new ApolloLink((operation, forward) => {
    //     const hasDefer = hasDirectives(['defer'], operation.query);
    //     let originalResult;
    //     return forward(operation).map((result) => {
    //         if (hasDefer) {
    //             if (!_.has(result, 'path')) {
    //                 originalResult = _.cloneDeep(result);
    //             } else {
    //                 _.set(originalResult, ['data', ...result.path], result.data);
    //                 return originalResult;
    //             }
    //         }
    //         return result;
    //     });
    // });

    const httpLink = from([
        // deferAdapterLink,
        createHttpLink({
            uri: httpEndpoint,
            ...httpLinkOptions,
        }),
    ]);

    const mainLink = split(
        (operation) => isMutationOperation(operation.query),
        uploadLink,
        httpLink
    );

    if (!link) {
        link = mainLink;
    } else if (defaultHttpLink) {
        link = from([link, mainLink]);
    }

    link = from([new SentryLink({
        attachBreadcrumbs: {
            includeQuery: false,
            includeVariables: true,
            includeError: true,
            transform: (breadcrumb, operation) => {
                if (breadcrumb.level === 'error') {
                    breadcrumb.data.variables = JSON.stringify(
                        obfuscate(operation.variables, [/\w*id$/i]),
                        null,
                        4
                    );
                } else {
                    delete breadcrumb.data.variables;
                }
                return breadcrumb;
            },
        },
    }), link]);

    // HTTP Auth header injection
    const authLink = setContext(async (_, { headers }) => {
        const Authorization = await getAuth(tokenName);
        const authorizationHeader = Authorization ? { Authorization } : {};
        return {
            headers: {
                ...headers,
                ...authorizationHeader,
            },
        };
    });

    // Concat all the http link parts
    link = authLink.concat(link);

    if (preAuthLinks.length) {
        link = from(preAuthLinks).concat(authLink);
    }

    // On the server, we don't want WebSockets and Upload links
    if (!ssr) {
    // If on the client, recover the injected state
        if (typeof window !== 'undefined') {
            // eslint-disable-next-line no-underscore-dangle
            const state = window.__APOLLO_STATE__;
            if (state && state[clientId]) {
                // Restore state
                cache.restore(state[clientId]);
            }
        }

        let persistingOpts = {};
        if (typeof persisting === 'object' && persisting != null) {
            persistingOpts = persisting;
            persisting = true;
        }
        if (persisting === true) {
            link = createPersistedQueryLink(persistingOpts).concat(link);
        }

        let wsLink;

        // Web socket
        // Create the subscription websocket link
        try {
            // eslint-disable-next-line no-shadow
            axios.interceptors.request.use((config) => {
                config.headers['x-socket-id'] = pusher.connection.socket_id;
                return config;
            });

            wsLink = from([
                new PusherLink({ pusher }),
                mainLink,
            ]);
        } catch (e) {
            // If we cannot instantiate pusher then we just report the error and
            // carry on without websockets.
            report(e);
            wsLink = new ApolloLink(() => {});
        }

        link = split(
            // split based on operation type
            ({ query }) => {
                const { kind, operation } = getMainDefinition(query);
                return kind === 'OperationDefinition' && operation === 'subscription';
            },
            wsLink,
            link
        );
    }

    const apolloClient = new ApolloClient({
        link,
        cache,
        // Additional options
        ...(ssr ? {
            // Set this on the server to optimize queries when SSR
            ssrMode: true,
        } : {
            // This will temporary disable query force-fetching
            ssrForceFetchDelay: 100,
            // Apollo devtools
            connectToDevTools: !isProduction,
        }),
        typeDefs,
        resolvers,
        ...apollo,
    });

    // Re-write the client state defaults on cache reset
    if (stateLink) {
        apolloClient.onResetStore(stateLink.writeDefaults);
    }

    if (onCacheInit) {
        onCacheInit(cache);
        apolloClient.onResetStore(() => onCacheInit(cache));
    }

    return {
        apolloClient,
        wsClient,
        stateLink,
    };
}

export function restartWebsockets(wsClient) {
    // Copy current operations
    const operations = { ...wsClient.operations };

    // Close connection
    wsClient.close(true);

    // Open a new one
    wsClient.connect();

    // Push all current operations to the new connection
    Object.keys(operations).forEach((id) => {
        wsClient.sendMessage(
            id,
            'start',
            operations[id].options
        );
    });
}
