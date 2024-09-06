import MARKER_GROUPS from '@/graphql/markers/queries/MarkerGroups.gql';
import UPDATE_MARKER_GROUP from '@/graphql/markers/mutations/UpdateMarkerGroup.gql';
import CREATE_MARKER_GROUP from '@/graphql/markers/mutations/CreateMarkerGroup.gql';
import DELETE_MARKER_GROUP from '@/graphql/markers/mutations/DeleteMarkerGroup.gql';
import CREATE_MARKER from '@/graphql/markers/mutations/CreateMarker.gql';
import UPDATE_MARKER from '@/graphql/markers/mutations/UpdateMarker.gql';
import DELETE_MARKER from '@/graphql/markers/mutations/DeleteMarker.gql';
import MOVE_MARKER from '@/graphql/markers/mutations/MoveMarker.gql';
import SET_MARKER from '@/graphql/markers/mutations/SetMarker.gql';
import REMOVE_MARKER from '@/graphql/markers/mutations/RemoveMarker.gql';
import initializeConnections from '@/http/apollo/initializeConnections.js';
import { validationFeedback } from '@/core/uiGenerators/userFeedbackGenerators.js';
import { getValidationMessages, isValidationError } from '@/http/checkResponse.js';
import { baseApolloClient } from '@/core/repositories/baseRepository.js';
import { getCachedOperationNames } from '@/core/helpers/apolloHelpers.js';

export function initializeMarkers(data) {
    return initializeConnections(data);
}

export function updateMarkerGroup(form) {
    return form.graphql(
        UPDATE_MARKER_GROUP
    );
}

export function createMarkerGroup(form) {
    return form.graphql(CREATE_MARKER_GROUP, {
        refetchQueries: [
            MARKER_GROUPS,
        ],
    });
}

export function deleteMarkerGroup(markerGroup) {
    const client = baseApolloClient();
    return client.mutate({
        mutation: DELETE_MARKER_GROUP,
        variables: {
            input: {
                id: markerGroup.id,
            },
        },
        refetchQueries: getCachedOperationNames([
            MARKER_GROUPS,
        ], client),
        update(cache) {
            if (markerGroup.usedByMappings) {
                markerGroup.usedByMappings.forEach((mapping) => {
                    cache.modify({
                        id: cache.identify(mapping),
                        fields: {
                            markerGroups(cachedMarkerGroups, { readField }) {
                                return cachedMarkerGroups
                                    && cachedMarkerGroups.filter((group) => {
                                        const groupId = cache.identify(readField('group', group));
                                        return groupId !== cache.identify(markerGroup);
                                    });
                            },
                        },
                    });
                    // In case the marker group is used in the design. This is
                    // removed in the server so we need to refetch.
                    mapping.pages.forEach((page) => {
                        ['defaultFilter', 'personalDefaultFilter'].forEach((fieldName) => {
                            const filterId = cache.data.getFieldValue(page[fieldName], 'id');
                            if (filterId) {
                                cache.evict({ id: cache.identify(page[fieldName]) });
                            }
                        });
                        cache.evict({ id: cache.identify(page), fieldName: 'design' });
                        cache.gc();
                    });
                });
            }
        },
    }).catch((error) => {
        if (isValidationError(error)) {
            const messages = getValidationMessages(error);
            if (messages['input.id']) {
                validationFeedback(messages['input.id']);
                return null;
            }
        }
        return Promise.reject(error);
    });
}

export function createMarker(form) {
    return form.graphql(CREATE_MARKER);
}

export function deleteMarker(marker) {
    return baseApolloClient().mutate({
        mutation: DELETE_MARKER,
        variables: {
            input: {
                groupId: marker.group.id,
                id: marker.id,
            },
        },
        update(cache) {
            cache.evict({ id: cache.identify(marker) });
            cache.gc();
        },
    }).catch((error) => {
        if (isValidationError(error)) {
            const messages = getValidationMessages(error);
            if (messages['input.id']) {
                validationFeedback(messages['input.id']);
                return null;
            }
        }
        return Promise.reject(error);
    });
}

export function updateMarker(form) {
    return form.graphql(UPDATE_MARKER);
}

export function moveMarker(marker, previousMarker) {
    const previousId = previousMarker?.id || null;

    baseApolloClient().mutate({
        mutation: MOVE_MARKER,
        variables: {
            input: {
                groupId: marker.group.id,
                id: marker.id,
                previousId,
            },
        },
    });
}

export const groupRepository = {
    updateGroup: updateMarkerGroup,
    deleteGroup: deleteMarkerGroup,
    createGroup: createMarkerGroup,
    updateGroupItem: updateMarker,
    createGroupItem: createMarker,
    deleteGroupItem: deleteMarker,
    moveGroupItem: moveMarker,
};

export function setMarker(item, marker) {
    return baseApolloClient().mutate({
        mutation: SET_MARKER,
        variables: {
            input: {
                markerId: marker.id,
                markableId: item.id,
            },
        },
    });
}

export function removeMarker(item, marker) {
    return baseApolloClient().mutate({
        mutation: REMOVE_MARKER,
        variables: {
            input: {
                markerId: marker.id,
                markableId: item.id,
            },
        },
    });
}
