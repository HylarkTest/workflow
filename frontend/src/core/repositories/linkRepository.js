import LINKS from '@/graphql/links/queries/Links.gql';
import GROUPED_LINKS from '@/graphql/links/queries/GroupedLinks.gql';
import UPDATE_LINK from '@/graphql/links/mutations/UpdateLink.gql';
import CREATE_LINK from '@/graphql/links/mutations/CreateLink.gql';
import DELETE_LINK from '@/graphql/links/mutations/DeleteLink.gql';
import DUPLICATE_LINK from '@/graphql/links/mutations/DuplicateLink.gql';
import MOVE_LINK from '@/graphql/links/mutations/MoveLink.gql';
import { instantiate } from '@/core/utils.js';
import initializeConnections from '@/http/apollo/initializeConnections.js';
import Link from '@/core/models/Link.js';
import LINK_LISTS from '@/graphql/links/queries/LinkLists.gql';
import LINK_STATS from '@/graphql/links/queries/LinkStats.gql';
import eventBus, { dispatchPromise } from '@/core/eventBus.js';
import { baseApolloClient } from '@/core/repositories/baseRepository.js';
import { getCachedOperationNames } from '@/core/helpers/apolloHelpers.js';

export const LINK_CREATED = Symbol('Link created');
export const LINK_UPDATED = Symbol('Link updated');
export const LINK_DELETED = Symbol('Link deleted');

export function createLinkFromObject(obj) {
    return instantiate(obj, Link);
}

export function initializeLinks(data) {
    return _.getFirstKey(initializeConnections(data));
}

export function moveLinkToList(link, list) {
    const client = baseApolloClient();
    client.mutate({
        mutation: MOVE_LINK,
        variables: {
            input: {
                linkListId: list.id,
                id: link.id,
            },
        },
        refetchQueries: getCachedOperationNames([
            LINK_LISTS,
            LINKS,
        ], client),
    });
}

export function updateLink(form) {
    return dispatchPromise(form.graphql(
        UPDATE_LINK
    ), LINK_UPDATED, 'data.updateLink.link');
}

export function duplicateLink(link, data) {
    const client = baseApolloClient();
    return client.mutate({
        mutation: DUPLICATE_LINK,
        variables: {
            input: {
                id: link.id,
                ...data,
            },
        },
        refetchQueries: getCachedOperationNames([
            LINKS,
            GROUPED_LINKS,
            LINK_LISTS,
            LINK_STATS,
        ], client),
    }).then((result) => {
        eventBus.dispatch(LINK_CREATED, result.data.duplicateLink.link);
        return result;
    });
}

export function changeLinkList(link, linkListId) {
    const client = baseApolloClient();
    return client.mutate({
        mutation: UPDATE_LINK,
        variables: {
            input: {
                linkListId,
                id: link.id,
            },
        },
        refetchQueries: getCachedOperationNames([
            LINK_LISTS,
            LINKS,
        ], client),
    });
}

export function createLink(form) {
    return dispatchPromise(form.graphql(CREATE_LINK, {
        refetchQueries: [
            LINKS,
            GROUPED_LINKS,
            LINK_LISTS,
            LINK_STATS,
        ],
        formatData(data) {
            return {
                ...data,
                associations: data.associations.map((item) => item?.id || item),
                assigneeGroups: (data.assigneeGroups || []).map((assigneeGroup) => ({
                    groupId: assigneeGroup.groupId,
                    assignees: _.map(assigneeGroup.assignees, 'id'),
                })),
            };
        },
    }), LINK_CREATED, 'data.createLink.link');
}

export function deleteLink(link) {
    const client = baseApolloClient();
    return client.mutate({
        mutation: DELETE_LINK,
        variables: {
            input: {
                id: link.id,
            },
        },
        refetchQueries: getCachedOperationNames([
            LINKS,
            GROUPED_LINKS,
            LINK_LISTS,
            LINK_STATS,
        ], client),
    }).then((result) => {
        eventBus.dispatch(LINK_DELETED, link);
        return result;
    });
}

export function toggleFavorite(link) {
    const client = baseApolloClient();
    return client.mutate({
        mutation: UPDATE_LINK,
        variables: {
            input: {
                id: link.id,
                isFavorite: !link.isFavorite,
            },
        },
        optimisticResponse: {
            updateLink: {
                code: '200',
                link: {
                    ...link,
                    isFavorite: !link.isFavorite,
                },
            },
        },
        refetchQueries: getCachedOperationNames([
            LINK_STATS,
        ], client),
    });
}
