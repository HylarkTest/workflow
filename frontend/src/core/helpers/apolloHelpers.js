import { getOperationName, relayStylePagination } from '@apollo/client/utilities';
import _, {
    camelCase, endsWith, every, has, orderBy, get,
} from 'lodash';
import { arrRemoveId, getFirstKey } from '@/core/utils.js';

export const getFieldNameArgs = _.memoize((fieldName) => {
    if (!endsWith(fieldName, ')')) {
        return {};
    }

    const argsStart = fieldName.indexOf('(') + 1;
    const argsString = fieldName.substr(argsStart, fieldName.length - argsStart - 1);
    return JSON.parse(argsString);
});

export function fieldNameHasArgs(fieldName, args) {
    const fieldNameArgs = getFieldNameArgs(fieldName);

    return every(args, (value, field) => {
        return has(fieldNameArgs, field) && fieldNameArgs[field] === value;
    });
}

export function updateOrder(edges, fieldName, readField, defaultOrder = [], fieldMap = {}) {
    const args = getFieldNameArgs(fieldName);

    const orders = args.orderBy || defaultOrder;

    const orderByArgs = _(orders).map(({ field, direction }) => {
        const iteratee = ({ node }) => {
            const key = fieldMap[field] || camelCase(field);
            return readField(key, node);
        };
        return [iteratee, direction.toLowerCase()];
    }).unzip().value();

    return orderBy(edges, ...orderByArgs);
}

export function removeTypename(data) {
    if (!_.isPlainObject(data) && !_.isArray(data)) {
        return data;
    }
    const result = _.isArray(data) ? [] : {};
    _.keys(data).forEach((key) => {
        if (key !== '__typename') {
            result[key] = removeTypename(data[key]);
        }
    });
    return result;
}

export function updateOrderInSpace(updatedModel, key, movedDown) {
    return (data) => {
        if (data) {
            const dataKey = key || _.firstKey(data);
            const edges = [...data[dataKey].edges];
            const foundModel = _.find(edges, ['node.id', updatedModel.id]);
            if (foundModel.node.order === -1 || !Number.isInteger(foundModel.node.order)) {
                return data;
            }

            const sameOrder = _.find(edges, [
                'node.order', foundModel.node.order,
                'node.space.id', foundModel.node.space.id,
            ]);

            if (sameOrder) {
                _.forEach(edges, (edge, index) => {
                    if (edge.node.order >= foundModel.node.order && edge.node.id !== foundModel.node.id) {
                        edges[index] = {
                            ...edge,
                            node: {
                                ...edge.node,
                                order: edge.node.order + (movedDown ? -1 : 1),
                            },
                        };
                    }
                });

                return {
                    [dataKey]: {
                        ...data[dataKey],
                        edges,
                    },
                };
            }
        }

        return data;
    };
}

/*
 * All mutations have a similar response structure. When we run the query with
 * Apollo, it can use an optimistic response to keep the interface snappy
 * (replacing it with the real data when the response comes back from the
 * server).
 * This function makes it easy to generate the optimistic response without
 * duplicating the structure that is common across all mutations.
 */
export function createOptimisticMutationResponse(mutationKey, extraData, status = '200') {
    return {
        __typename: 'Mutation',
        [mutationKey]: {
            code: status,
            success: true,
            ...extraData,
        },
    };
}

/**
 * Many times mutations have an effect on other queries stored in apollo's
 * cache.
 * For example, creating or deleting a resource would then change the result of
 * any query that fetches a group of resources that would include the
 * new/removed resource.
 * Many times this includes fetching an item from the response and using that
 * to modify something in the cache.
 * This function returns a callback that can be used for the `update` option in
 * an apollo mutation.
 *
 * @param query: The query object that identifies the cached response to be updated
 * @param responsePath: A string that will be used to fetch the important data from the response
 * @param storePath: A string that will be used to fetch the original data from the cache to be updated
 * @param updateCallback: A callback that accepts the cached data and the item from the response, that should return
 *                        the new data to be updated in the cache
 */
export function updateQueryWithResponse(query, responsePath, storePath, updateCallback) {
    return (store, response) => {
        const responseItem = get(response, `data.${responsePath}`);
        store.updateQuery(
            query,
            (data) => {
                if (!data) {
                    return data;
                }
                const dataKey = storePath || _.firstKey(data);
                const originalData = data[dataKey];
                const newData = updateCallback(originalData, responseItem);
                return {
                    ...data,
                    [dataKey]: newData,
                };
            }
        );
    };
}

/*
 * A lot of mutation operations in apollo involve appending a node to a cursor
 * based connection.
 */
export function addNodeToQueryConnectionCallback(query, responsePath, storePath, edgeTypeName) {
    return updateQueryWithResponse(query, responsePath, storePath, (originalConnection, node) => {
        const typename = edgeTypeName || originalConnection.__typename.replace('Connection', 'Edge');
        return {
            ...originalConnection,
            edges: [...originalConnection.edges, { __typename: typename, node }],
        };
    });
}

/*
 * Some mutation operations in apollo involve appending items to an offset based
 * array.
 */
export function addToQueryOffsetCallback(query, responsePath, storePath) {
    return updateQueryWithResponse(query, responsePath, storePath, (originalData, item) => {
        return {
            ...originalData,
            data: [
                ...originalData.data,
                item,
            ],
        };
    });
}

/*
 * This method will return a callback that removes the node with the specified
 * ID from the cache. Using cursor based pagination.
 */
export function removeNodeFromQueryConnectionCallback(query, storePath, id) {
    return updateQueryWithResponse(query, null, storePath, (originalData) => {
        return {
            ...originalData,
            edges: arrRemoveId(originalData.edges, id, 'node.id'),
        };
    });
}

/*
 * This method will return a callback that removes the item with the specified
 * ID from the cache. Using offset based pagination.
 */
export function removeItemFromQueryOffsetCallback(query, storePath, id) {
    return updateQueryWithResponse(query, null, storePath, (originalData) => {
        return {
            ...originalData,
            data: arrRemoveId(originalData.data, id),
        };
    });
}

export function offsetLimitPagination(keyArgs) {
    return {
        keyArgs,
        merge(existing, incoming, { args }) {
            const merged = existing ? existing.data.slice(0) : [];

            if (incoming) {
                // Assume an offset of 0 if args.offset omitted.
                const { page = 1, first } = args;
                for (let i = 0; i < incoming.data.length; i += 1) {
                    merged[((page - 1) * first) + i] = incoming.data[i];
                }
            }

            return {
                ...incoming,
                data: merged,
            };
        },
    };
}

export function groupedRelayStylePagination(keyArgs) {
    const relayStyle = relayStylePagination(keyArgs);
    return {
        keyArgs,
        read(existing, options) {
            if (!existing) {
                return existing;
            }
            return {
                ...existing,
                groups: existing.groups.map((group) => relayStyle.read(group, options)),
            };
        },
        merge(existing, incoming, options) {
            if (!existing) {
                return relayStyle.merge(null, incoming, options);
            }

            const groups = options.args.includeGroups?.length ? existing.groups : incoming.groups;

            return {
                ...incoming,
                groups: groups.map(({ groupHeader }) => {
                    const existingGroup = _.find(existing.groups, ['groupHeader', groupHeader]);
                    const incomingGroup = _.find(incoming.groups, ['groupHeader', groupHeader]);
                    if (_.isUndefined(incomingGroup) || _.isUndefined(existingGroup)) {
                        return incomingGroup || existingGroup;
                    }
                    return relayStyle.merge(existingGroup, incomingGroup, options);
                }),
            };
        },
    };
}

export function refetchOnSubscription(subscription, query) {
    query.subscribeToMore({
        subscription,
        updateQuery: (__, { subscriptionData: { data } }) => {
            if (!_.isEmpty(getFirstKey(data))) {
                query.refetch();
            }
        },
    });
}

export function filterCachedQueries(operationNames, client) {
    const cachedQueries = Array.from(
        client.queryManager
            .getObservableQueries('all')
            .values()
    ).map(({ queryName }) => queryName);

    return _.intersection(operationNames, cachedQueries);
}

export function getCachedOperationNames(queries, client) {
    return filterCachedQueries(
        queries.map((query) => (_.isString(query) ? query : getOperationName(query))),
        client
    );
}

export function subscribeToUpdates(client, subscriptions, queriesToRefetch, ignoredSubscriptions) {
    // Register relevant subscriptions
    const subscriptionsArr = subscriptions.map((document) => {
        return client.subscribe({ query: document }).subscribe(({ data }) => {
            // Do not take any action when ignoredSubscriptions are triggered. That would usually include the `update`
            // subscription as Apollo finds and updates existing items automatically.
            if (ignoredSubscriptions.includes(document)) {
                return;
            }
            // Otherwise, refetch relevant queries when remaining subscribed actions are triggered
            if (!_.isEmpty(getFirstKey(data))) {
                const cachedQueries = getCachedOperationNames(queriesToRefetch, client);
                client.refetchQueries({ include: cachedQueries });
            }
        });
    });
    // Callback returned to unsubscribe when component is destroyed
    return () => subscriptionsArr.forEach((subscription) => subscription.unsubscribe());
}

export function createDynamicSmartQuery(vm, key, queryCb, options, method = 'addSmartQuery') {
    let smartQuery;
    vm.$watch(
        queryCb,
        {
            immediate: true,
            handler(val, old) {
                if (!smartQuery) {
                    smartQuery = vm.$apollo[method](key, {
                        query: val,
                        ...options,
                    });
                } else if (val.loc.source.body !== old?.loc.source.body) {
                    smartQuery.options.query = val;
                    smartQuery.refresh();
                }
            },
        }
    );
}

export function createDynamicSmartSubscription(vm, key, queryCb, options) {
    createDynamicSmartQuery(
        vm,
        key,
        queryCb,
        { fetchPolicy: 'cache-only', ...options },
        'addSmartSubscription'
    );
}
