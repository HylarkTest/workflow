import CREATE_SPACE from '@/graphql/spaces/mutations/CreateSpace.gql';
import UPDATE_SPACE from '@/graphql/spaces/mutations/UpdateSpace.gql';
import DELETE_SPACE from '@/graphql/spaces/mutations/DeleteSpace.gql';
import SPACES from '@/graphql/spaces/queries/Spaces.gql';
import PAGES from '@/graphql/pages/queries/Pages.gql';
import {
    addNodeToQueryConnectionCallback,
    createOptimisticMutationResponse,
    removeNodeFromQueryConnectionCallback,
    updateQueryWithResponse,
} from '@/core/helpers/apolloHelpers.js';
import { baseApolloClient } from '@/core/repositories/baseRepository.js';

export function createSpace(form) {
    return form.graphql(
        CREATE_SPACE,
        {
            update(store, response) {
                addNodeToQueryConnectionCallback(
                    { query: SPACES },
                    'createSpace.space',
                    'spaces'
                )(store, response);
                updateQueryWithResponse(
                    { query: PAGES },
                    'createSpace.space',
                    'spaces',
                    (originalConnection, node) => {
                        return {
                            ...originalConnection,
                            edges: [
                                ...originalConnection.edges,
                                {
                                    __typename: 'SpaceEdge',
                                    node: {
                                        ...node,
                                        pages: {
                                            __typename: 'PageConnection',
                                            edges: [],
                                        },
                                    },
                                },
                            ],
                        };
                    }
                )(store, response);
            },
        }
    );
}

export function updateSpace(form) {
    return form.graphql(
        UPDATE_SPACE,
        {
            optimisticResponse: createOptimisticMutationResponse('updateSpace', {
                space: {
                    __typename: 'Space',
                    id: form.id,
                    name: form.name,
                    createdAt: (new Date()).toISOString(),
                },
            }),
        }
    );
}

export function deleteSpace(space) {
    return baseApolloClient().mutate({
        mutation: DELETE_SPACE,
        variables: {
            input: { id: space.id },
        },
        update: _.over([
            removeNodeFromQueryConnectionCallback({ query: SPACES }, 'spaces', space.id),
            removeNodeFromQueryConnectionCallback({ query: PAGES }, 'spaces', space.id),
        ]),
    });
}
