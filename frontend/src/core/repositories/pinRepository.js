import PINS from '@/graphql/pinboard/queries/Pins.gql';
import GROUPED_PINS from '@/graphql/pinboard/queries/GroupedPins.gql';
import UPDATE_PIN from '@/graphql/pinboard/mutations/UpdatePin.gql';
import CREATE_PIN from '@/graphql/pinboard/mutations/CreatePin.gql';
import DELETE_PIN from '@/graphql/pinboard/mutations/DeletePin.gql';
import DUPLICATE_PIN from '@/graphql/pinboard/mutations/DuplicatePin.gql';
import MOVE_PIN from '@/graphql/pinboard/mutations/MovePin.gql';
import { instantiate } from '@/core/utils.js';
import initializeConnections from '@/http/apollo/initializeConnections.js';
import Pin from '@/core/models/Pin.js';
import PINBOARDS from '@/graphql/pinboard/queries/Pinboards.gql';
import PIN_STATS from '@/graphql/pinboard/queries/PinStats.gql';
import eventBus, { dispatchPromise } from '@/core/eventBus.js';
import { baseApolloClient } from '@/core/repositories/baseRepository.js';
import { getCachedOperationNames } from '@/core/helpers/apolloHelpers.js';

export const PIN_CREATED = Symbol('Pin created');
export const PIN_UPDATED = Symbol('Pin updated');
export const PIN_DELETED = Symbol('Pin deleted');

export function createPinFromObject(obj) {
    return instantiate(obj, Pin);
}

export function initializePins(data) {
    return _.getFirstKey(initializeConnections(data));
}

export function movePinToList(pin, list) {
    const client = baseApolloClient();
    client.mutate({
        mutation: MOVE_PIN,
        variables: {
            input: {
                pinboardId: list.id,
                id: pin.id,
            },
        },
        refetchQueries: getCachedOperationNames([
            PINBOARDS,
            PINS,
        ], client),
    });
}

export function updatePin(form) {
    return dispatchPromise(form.post({
        query: UPDATE_PIN,
    }), PIN_UPDATED, 'data.updatePin.pin');
}
export function duplicatePin(pin, data) {
    const client = baseApolloClient();

    return client.mutate({
        mutation: DUPLICATE_PIN,
        variables: {
            input: {
                id: pin.id,
                ...data,
            },
        },
        refetchQueries: getCachedOperationNames([
            PINS,
            GROUPED_PINS,
            PINBOARDS,
            PIN_STATS,
        ], client),
    }).then((result) => {
        eventBus.dispatch(PIN_CREATED, result.data.duplicatePin.pin);
        return result;
    });
}

export function changePinboard(pin, pinboardId) {
    const client = baseApolloClient();
    return client.mutate({
        mutation: UPDATE_PIN,
        variables: {
            input: {
                pinboardId,
                id: pin.id,
            },
        },
        refetchQueries: getCachedOperationNames([
            PINBOARDS,
            PINS,
        ], client),
    });
}

export function createPin(form) {
    return dispatchPromise(form.post({
        query: CREATE_PIN,
        refetchQueries: [
            PINS,
            GROUPED_PINS,
            PINBOARDS,
            PIN_STATS,
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
    }), PIN_CREATED, 'data.createPin.pin');
}

export function deletePin(pin) {
    const client = baseApolloClient();
    return client.mutate({
        mutation: DELETE_PIN,
        variables: {
            input: {
                id: pin.id,
            },
        },
        refetchQueries: getCachedOperationNames([
            PINS,
            GROUPED_PINS,
            PINBOARDS,
            PIN_STATS,
        ], client),
    }).then((result) => {
        eventBus.dispatch(PIN_DELETED, pin);
        return result;
    });
}

export function toggleFavorite(pin) {
    const client = baseApolloClient();
    return client.mutate({
        mutation: UPDATE_PIN,
        variables: {
            input: {
                id: pin.id,
                isFavorite: !pin.isFavorite,
            },
        },
        optimisticResponse: {
            updatePin: {
                code: '200',
                pin: {
                    ...pin,
                    isFavorite: !pin.isFavorite,
                },
            },
        },
        refetchQueries: getCachedOperationNames([
            PIN_STATS,
        ], client),
    });
}
