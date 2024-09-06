import {
    addNodeToQueryConnectionCallback,
    createOptimisticMutationResponse,
    getCachedOperationNames,
    removeNodeFromQueryConnectionCallback,
    updateOrderInSpace,
} from '@/core/helpers/apolloHelpers.js';
import { arrRemove, arrRemoveId } from '@/core/utils.js';
import { baseApolloClient } from '@/core/repositories/baseRepository.js';

export function initializeLists(data, initializeCb) {
    const dataKey = _.firstKey(data);
    return _(data[dataKey].edges).map((edge) => initializeCb(edge.node))
        .groupBy('space.id')
        .map((lists) => {
            return {
                space: _.first(lists).space,
                lists: _.sortBy(lists, 'order'),
            };
        })
        .sortBy('space.createdAt')
        .value();
}

export function createList(form, createQuery, listQuery, initializeCb) {
    return form.graphql(
        createQuery,
        {
            update: addNodeToQueryConnectionCallback(
                { query: listQuery },
                'createList.list'
            ),
            refetchQueries: [listQuery],
        }
    ).then((response) => {
        return initializeCb(_.getFirstKey(response.data).list);
    });
}

export function updateList(form, list, updateQuery, optimisticResponse = null) {
    return form.graphql(
        updateQuery,
        {
            optimisticResponse: createOptimisticMutationResponse('updateList', {
                list: {
                    ...(_.pick(form, ['id', 'name', 'color'])),
                    ...(_.pick(list, ['order', 'count', 'isDefault', 'space'])),
                    ...(optimisticResponse || {}),
                },
            }),
        }
    );
}

export function deleteList(list, deleteQuery, listQuery, statQuery) {
    const client = baseApolloClient();
    return client.mutate({
        mutation: deleteQuery,
        variables: {
            input: { id: list.id },
        },
        update(cache, response) {
            removeNodeFromQueryConnectionCallback({ query: listQuery }, null, list.id)(cache, response);

            _.forEach(cache.data.data, (page, key) => {
                if (key.startsWith('ListPage:') && page.lists?.length && page.lists.includes(list.id)) {
                    const lists = arrRemove(page.lists, list.id);
                    const listQueryData = _.cloneDeep(cache.readQuery({
                        query: listQuery,
                        variables: {
                            forLists: page.lists,
                            spaceIds: [page.space.id],
                        },
                    }));
                    if (listQueryData) {
                        const dataKey = _.firstKey(listQueryData);
                        listQueryData[dataKey].edges = arrRemoveId(listQueryData[dataKey].edges, list.id, 'node.id');
                        cache.writeQuery({
                            query: listQuery,
                            variables: {
                                forLists: lists,
                                spaceIds: [page.space.id],
                            },
                            listQueryData,
                        });
                    }
                    cache.modify({
                        id: cache.identify(page),
                        fields: {
                            lists: () => lists,
                        },
                    });
                }
            });
        },
        refetchQueries: getCachedOperationNames([
            listQuery,
            ...(statQuery ? [statQuery] : []),
        ], client),
    });
}

export function moveList(list, previousList, moveListQuery, listQuery) {
    const movedDown = previousList && previousList.order > list.order;
    const client = baseApolloClient();
    return client.mutate({
        mutation: moveListQuery,
        variables: {
            input: {
                id: list.id,
                previousId: previousList?.id || null,
            },
        },
        update: (store) => {
            store.updateQuery(
                { query: listQuery },
                updateOrderInSpace(list, null, movedDown)
            );
        },
        optimisticResponse: createOptimisticMutationResponse('moveList', {
            list: {
                ...list,
                order: previousList ? previousList.order + 0.5 : -1,
            },
        }),
        refetchQueries: getCachedOperationNames([listQuery], client),
    });
}
