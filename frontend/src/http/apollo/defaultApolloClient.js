import _ from 'lodash';
import { relayStylePagination } from '@apollo/client/utilities';
import { gql } from '@apollo/client';
import { createApolloClient, initializeCSRF } from '@/http/apollo/graphqlClient.js';
import config from '@/core/config.js';
import possibleTypes from '@/../possibleTypes.json';
import { offsetLimitPagination, groupedRelayStylePagination } from '@/core/helpers/apolloHelpers.js';
// import { resetPossibleTypes, updatePossibleTypes } from '@/http/apollo/updatePossibleTypes.js';
import CLIENT_SCHEMA from '@/graphql/client/schema.graphql';
import { get, store } from '@/core/localStorage.js';
import { isHttpError } from '@/http/checkResponse.js';
import { doesCookieExist } from '@/core/helpers/cookieHelpers.js';

export const AUTH_TOKEN = 'apollo-token';

// Http endpoint
export const httpEndpoint = config('graphql.http');
// Files URL root
export const filesRoot = config('graphql.file');

function createFieldPolicies(fields, prefix, policyName, policies) {
    fields?.forEach((field) => {
        if (field.type === 'MULTI') {
            const subType = _.upperFirst(field.apiName);
            const newPrefix = `${prefix}${subType}`;
            const newPolicyName = `${newPrefix}Multi`;
            // eslint-disable-next-line no-param-reassign
            policies[newPolicyName] = { merge: true, fields: {} };
            createFieldPolicies(field.options.fields, newPrefix, newPolicyName, policies);
        }
        if (field.type === 'CATEGORY') {
            if (!field.options.multiSelect && !field.options.list) {
                const policy = policies[policyName];

                policy.fields[field.apiName] = (fieldValue, { canRead }) => {
                    return canRead(fieldValue) ? fieldValue : null;
                };
            }
        }
    });
}

function addDynamicTypePolicies(name, { cache, readField }) {
    const type = _.upperFirst(name);
    const policies = {
        [`${type}ItemData`]: { merge: true, fields: {} },
        [`${type}ItemFields`]: { merge: true },
        [`${type}ItemMarkers`]: { merge: true, fields: {} },
        [`${type}ItemFeatures`]: { merge: true },
        [`${type}Relations`]: { merge: true },
        [`${type}Item`]: {
            merge: true,
            fields: {
                markerGroups: { merge: false },
                assigneeGroups: { merge: false },
                features: { merge: true },
                image: { merge: true },
            },
        },
    };

    const fields = readField('fields');
    createFieldPolicies(fields, type, `${type}ItemData`, policies);
    const markerGroups = readField('markerGroups');
    markerGroups?.forEach((group) => {
        const markerGroup = readField('group', group);
        const groupType = readField('type', markerGroup);
        if (groupType === 'STATUS') {
            const apiName = readField('apiName', group);
            policies[`${type}ItemMarkers`].fields[apiName] = (fieldValue, { canRead }) => {
                return canRead(fieldValue) ? fieldValue : null;
            };
        }
    });

    // Probably isn't necessary
    // const relationships = readField('relationships');
    // relationships.forEach((ref) => {
    //     const relationship = cache.data.data[ref.__ref];
    //     const relationshipType = _.upperFirst(relationship.apiName);
    //     if (_.endsWith(relationship.type, 'TO_MANY')) {
    //         policies[`${type}${relationshipType}RelationConnection`] = { merge: true };
    //     } else {
    //         policies[`${type}${relationshipType}RelationEdge`] = { merge: true };
    //     }
    // });

    cache.policies.addTypePolicies({
        ...policies,
        ItemQuery: {
            fields: {
                [readField('apiName')]: relayStylePagination([
                    'orderBy', 'search', 'filter', 'forRelation', 'markers', 'fields',
                ]),
                [name]: (item, { canRead }) => {
                    return canRead(item) ? item : null;
                },
            },
        },
        GroupedItemQuery: {
            fields: {
                [readField('apiName')]: groupedRelayStylePagination([
                    'orderBy', 'search', 'filter', 'forRelation', 'markers', 'fields', 'group',
                ]),
            },
        },
    });
    const itemType = `${type}Item`;
    cache.policies.addPossibleTypes({
        Node: [itemType],
        Item: [itemType],
        Assignable: [itemType],
        Markable: [itemType],
        Findable: [itemType],
        ActionSubject: [itemType],
        FetchesActions: [itemType],
    });
}

const commonArgs = ['forNode', 'forMapping'];

const commonListArgs = [
    ...commonArgs, 'spaceIds', 'forLists', 'refs',
];

const commonStatsArgs = [
    ...commonArgs, 'spaceId', 'forLists',
];

const commonListItemArgs = [
    ...commonArgs, 'search', 'filters', 'orderBy', 'markers',
];

const commonGroupedListItemArgs = [
    ...commonListItemArgs, 'group',
];

export const defaultOptions = {
    // You can use `https` for secure connection (recommended in production)
    httpEndpoint,
    httpLinkOptions: {
        async fetch(url, options) {
            try {
                await initializeCSRF();
            } catch (error) {
                initializeCSRF.flush();
                if (!isHttpError(error)) {
                    throw error;
                }
            }
            const csrfTokenCookie = doesCookieExist('XSRF-TOKEN');
            const token = csrfTokenCookie ? decodeURIComponent(csrfTokenCookie) : null;
            return fetch(url, {
                ...options,
                headers: {
                    ...options.headers,
                    'X-XSRF-TOKEN': token,
                },
            });
        },
    },
    // You can use `wss` for secure connection (recommended in production)
    // Use `null` to disable subscriptions
    // wsEndpoint: import.meta.env.VITE_GRAPHQL_WS || 'ws://localhost:4000/graphql',
    wsEndpoint: null,
    // LocalStorage token
    // tokenName: AUTH_TOKEN,
    // Enable Automatic Query persisting with Apollo Engine
    persisting: false,
    // Use websockets for everything (no HTTP)
    // You need to pass a `wsEndpoint` for this to work
    websocketsOnly: false,
    // Is being rendered on the server?
    ssr: false,

    // Override default apollo link
    // note: don't override httpLink here, specify httpLink options in the
    // httpLinkOptions property of defaultOptions.
    // link: myLink

    // Override default cache
    inMemoryCacheOptions: {
        possibleTypes,
        typePolicies: {
            Relationship: {
                keyFields: ['id', 'isInverse'],
            },
            MappingTagGroup: {
                keyFields: (object) => `${object.apiName}_${object.id}`,
            },
            Mapping: {
                fields: {
                    apiSingularName: (name, utils) => {
                        addDynamicTypePolicies(name, utils);
                        return name;
                    },
                    fields: { merge: false },
                    markerGroups: { merge: false },
                    relationships: { merge: false },
                    features: { merge: false },
                    pages: { merge: false },
                },
            },
            Field: {
                keyFields: false,
                fields: {
                    val(__, { readField }) {
                        return readField('meta')?.display || readField('type') || null;
                    },
                    options(options, { readField }) {
                        const type = readField('type');
                        if (type === 'MULTI') {
                            return {
                                ...options,
                                fields: options.fields.map((field) => ({
                                    ...field,
                                    val: field.meta?.display || field.type,
                                })),
                            };
                        }
                        return options;
                    },
                },
            },
            CategoryItemListValue: {
                fields: {
                    listValue(list, { canRead }) {
                        return list.filter(({ fieldValue }) => canRead(fieldValue));
                    },
                },
            },
            Markable: {
                fields: {
                    markerGroups(markerGroups, { canRead }) {
                        return markerGroups.filter((markerGroup) => {
                            return markerGroup.__typename !== 'StatusMarkerCollection' || canRead(markerGroup.marker);
                        });
                    },
                },
            },
            BaseEdge: {
                fields: {
                    preferences: { merge: false },
                },
            },
            MarkerGroup: {
                fields: {
                    markers: { merge: false },
                    usedByMappings: { merge: false },
                },
            },
            Category: {
                fields: {
                    items: { merge: false },
                },
            },
            Base: {
                fields: {
                    members: { merge: false },
                    invites: { merge: false },
                    preferences: { merge: false },
                },
            },
            Mailbox: {
                merge: true,
            },
            ...(['Todo', 'Event', 'Note', 'Pin', 'Link', 'Document'].reduce((acc, type) => ({
                ...(acc || {}),
                [type]: {
                    fields: {
                        markerGroups: { merge: false },
                        assigneeGroups: { merge: false },
                    },
                },
            }), {})),
            Recurrence: {
                fields: {
                    byDay(val) {
                        return val?.map((day) => ['SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA'].indexOf(day)) || null;
                    },
                },
            },
            PageInfo: {
                fields: {
                    hasFilterApplied(__, { readField }) {
                        const rawTotal = readField('rawTotal');
                        const total = readField('total');
                        return rawTotal !== null && total !== null && rawTotal !== total;
                    },
                },
            },
            Associatable: {
                fields: {
                    associations: {
                        merge: false,
                    },
                },
            },
            User: {
                fields: {
                    plan: { merge: true },
                    bases: { merge: false },
                },
            },
            ItemQuery: { merge: true },
            ItemSubscription: { merge: true },
            GroupedItemQuery: { merge: true },
            TagMarkerCollection: { merge: false },
            Query: {
                fields: {
                    savedFilter: {
                        read(__, { args, toReference }) {
                            return toReference({
                                __typename: 'SavedFilter',
                                id: args.id,
                            });
                        },
                    },
                    history: relayStylePagination([
                        'forNode', 'subjectType', 'type', 'search', 'orderBy',
                    ]),
                    todoLists: relayStylePagination(commonListArgs),
                    todoStats: relayStylePagination(commonStatsArgs),
                    todos: relayStylePagination([
                        ...commonListItemArgs,
                        'todoListId', 'dueAfter', 'dueBefore', 'isScheduled',
                        'minPriority', 'maxPriority', 'isCompleted',
                    ]),
                    groupedTodos: groupedRelayStylePagination([
                        ...commonGroupedListItemArgs,
                        'todoListId', 'dueAfter', 'dueBefore', 'isScheduled',
                        'minPriority', 'maxPriority', 'isCompleted',
                    ]),
                    calendars: relayStylePagination(commonListArgs),
                    events: relayStylePagination([
                        ...commonListItemArgs,
                        'includeRecurringInstances', 'calendarId',
                        'startsBefore', 'endsAfter',
                    ]),
                    groupedEvents: groupedRelayStylePagination([
                        ...commonGroupedListItemArgs,
                        'includeRecurringInstances', 'calendarId',
                        'startsBefore', 'endsAfter',
                    ]),
                    notebooks: relayStylePagination(commonListArgs),
                    noteStats: relayStylePagination(commonStatsArgs),
                    notes: relayStylePagination([
                        ...commonListItemArgs, 'notebookId',
                    ]),
                    groupedNotes: groupedRelayStylePagination([
                        ...commonGroupedListItemArgs, 'notebookId',
                    ]),
                    pinboards: relayStylePagination(commonListArgs),
                    pinStats: relayStylePagination(commonStatsArgs),
                    pins: relayStylePagination([
                        ...commonListItemArgs, 'pinboardId',
                    ]),
                    groupedPins: groupedRelayStylePagination([
                        ...commonGroupedListItemArgs, 'pinboardId',
                    ]),
                    linkLists: relayStylePagination(commonListArgs),
                    linkStats: relayStylePagination(commonStatsArgs),
                    links: relayStylePagination([
                        ...commonListItemArgs, 'linkListId',
                    ]),
                    groupedLinks: groupedRelayStylePagination([
                        ...commonGroupedListItemArgs, 'linkListId',
                    ]),
                    drives: relayStylePagination(commonListArgs),
                    documentStats: relayStylePagination(commonStatsArgs),
                    documents: relayStylePagination([
                        ...commonListItemArgs, 'driveId',
                    ]),
                    groupedDocuments: groupedRelayStylePagination([
                        ...commonGroupedListItemArgs, 'driveId',
                    ]),
                    emails: relayStylePagination(['mailboxId', 'sourceId', 'search', 'forNode']),
                    groupedEmails: groupedRelayStylePagination(['group', 'mailboxId', 'sourceId', 'search', 'forNode']),
                    externalTodos: offsetLimitPagination([
                        'todoListId', 'sourceId', 'filter', 'forNode',
                    ]),
                    externalEvents: offsetLimitPagination([
                        'calendarId', 'sourceId', 'endsAfter', 'startsBefore', 'forNode',
                    ]),
                    notifications: relayStylePagination([
                        'filter', 'channel',
                    ]),
                    mappings: relayStylePagination([
                        'spaceId', 'type', 'name',
                    ]),
                    savedFilters: relayStylePagination([
                        'nodeId', 'privacy', 'search',
                    ]),
                    imports: relayStylePagination(),
                    integrations: {
                        merge: false,
                    },
                    ui: {
                        merge: false,
                    },
                    activeTips: {
                        merge: false,
                    },
                },
            },
            Page: {
                fields: {
                    // overrides how the image field is merged due to type conflict
                    // with other types that have an image field,
                    // such as Item and Document
                    image(val, { readField }) {
                        return val || readField('_image') || null;
                    },
                },
            },
        },
    },
    typeDefs: CLIENT_SCHEMA,
    resolvers: {
        Mutation: {
            toggleExtendedNav: (__, ___, { cache }) => {
                const query = gql`query { ui { isNavExtended } }`;
                const ui = cache.readQuery({ query })?.ui;
                const data = {
                    ui: {
                        isNavExtended: !(ui?.isNavExtended),
                    },
                };
                cache.writeQuery({ query, data });
                store('ui', data.ui);
                return !!data.ui.isNavExtended;
            },
            updateActiveTips: (__, { input }, { cache }) => {
                const query = gql`query { activeTips { val tipTitle tipText active } }`;
                const data = {
                    activeTips: (input.tips || []).map((tip) => ({ active: false, ...tip })),
                };
                cache.writeQuery({ query, data });
                return data.activeTips;
            },
        },
        Query: {
            ui: async (__, ___, { cache }) => {
                const query = gql`query { ui { isNavExtended } }`;
                let ui = cache.readQuery({ query })?.ui;
                if (!ui) {
                    ui = await get('ui');
                }

                let extensionClass;

                if (ui?.isNavExtended) {
                    extensionClass = 'nav-extension--extended';
                } else {
                    extensionClass = 'nav-extension--mini';
                }

                return {
                    isNavExtended: !!(ui?.isNavExtended),
                    navExtensionClass: extensionClass,
                };
            },
        },
    },

    // Override the way the Authorization header is set
    // getAuth: (tokenName) => ...

    // Additional ApolloClient options
    // apollo: { ... }

    // Client local data (see apollo-link-state)
    // clientState: { resolvers: { ... }, defaults: { ... } }
};

let client;

export function defaultApolloClient() {
    if (!client) {
        client = createApolloClient(defaultOptions).apolloClient;
    }
    return client;
}

// Manually call this when user log in
export async function onLogin(apolloClient) {
    try {
        await apolloClient.resetStore();
    } catch (e) {
        // eslint-disable-next-line no-console
        console.log('%cError on cache reset (login)', 'color: orange;', e.message);
    }
}

// Manually call this when user log out
export function onLogout() {
    // Wait a couple of seconds before resetting the store, otherwise a bunch
    // of queries will be re-run.
    // apolloClient.resetStore()
    //     .catch((e) => {
    //         // eslint-disable-next-line no-console
    //         console.log('%cError on cache reset (logout)', 'color: orange;', e.message);
    //     });
}
