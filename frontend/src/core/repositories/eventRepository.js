import EVENTS from '@/graphql/calendar/queries/Events.gql';
import EXTERNAL_EVENTS from '@/graphql/calendar/queries/ExternalEvents.gql';
import EXTERNAL_CALENDARS from '@/graphql/calendar/queries/ExternalCalendars.gql';
import UPDATE_EVENT from '@/graphql/calendar/mutations/UpdateEvent.gql';
import UPDATE_EXTERNAL_EVENT from '@/graphql/calendar/mutations/UpdateExternalEvent.gql';
import CREATE_EVENT from '@/graphql/calendar/mutations/CreateEvent.gql';
import CREATE_EXTERNAL_EVENT from '@/graphql/calendar/mutations/CreateExternalEvent.gql';
import DELETE_EVENT from '@/graphql/calendar/mutations/DeleteEvent.gql';
import MOVE_EVENT from '@/graphql/calendar/mutations/MoveEvent.gql';
import DELETE_EXTERNAL_EVENT from '@/graphql/calendar/mutations/DeleteExternalEvent.gql';
import DUPLICATE_EVENT from '@/graphql/calendar/mutations/DuplicateEvent.gql';
import { instantiate } from '@/core/utils.js';
import { getCachedOperationNames, removeTypename } from '@/core/helpers/apolloHelpers.js';
import initializeConnections from '@/http/apollo/initializeConnections.js';
import Event from '@/core/models/Event.js';
import eventBus, { dispatchPromise } from '@/core/eventBus.js';
import { baseApolloClient } from '@/core/repositories/baseRepository.js';
import { checkAndHandleMissingError } from '@/http/exceptionHandler.js';

export const EVENT_CREATED = Symbol('Event created');
export const EVENT_UPDATED = Symbol('Event updated');
export const EVENT_DELETED = Symbol('Event deleted');

export function createEventFromObject(obj) {
    return instantiate(obj, Event);
}

const weekDayMap = ['SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA'];

export function initializeEvents(data) {
    if (_.has(data, 'externalEvents')) {
        return {
            ...data.externalEvents,
            data: data.externalEvents.data.map((node) => createEventFromObject(node)),
        };
    }
    return _.getFirstKey(initializeConnections(data));
}

export function moveEventToList(event, list) {
    const client = baseApolloClient();
    client.mutate({
        mutation: MOVE_EVENT,
        variables: {
            input: {
                calendarId: list.id,
                id: event.id,
            },
        },
        refetchQueries: getCachedOperationNames([
            EVENTS,
        ], client),
    });
}

export function updateInternalEvent(form, allFuture = false) {
    return dispatchPromise(form.graphql(
        UPDATE_EVENT,
        {
            formatData(dataWithTypename) {
                const data = removeTypename(dataWithTypename);
                if (_.has(data, 'recurrence')) {
                    if (!data.recurrence?.frequency) {
                        data.recurrence = null;
                    } else if (data.recurrence.byDay?.length) {
                        data.recurrence.byDay = data.recurrence.byDay.map((index) => weekDayMap[index]);
                    }
                }
                if (allFuture) {
                    data.thisAndFuture = true;
                }
                return data;
            },
            refetchQueries: [EVENTS],
        }
    ), EVENT_UPDATED, 'data.updateEvent.event');
}

export function updateExternalEvent(form, primaryId, all = false) {
    return dispatchPromise(form.graphql(
        UPDATE_EXTERNAL_EVENT,
        {
            formatData(dataWithTypename) {
                const data = removeTypename(dataWithTypename);
                if (_.has(data, 'recurrence')) {
                    if (!data.recurrence?.frequency) {
                        return {
                            ...data,
                            recurrence: null,
                        };
                    }
                    if (data.recurrence.byDay) {
                        data.recurrence.byDay = data.recurrence.byDay.map((day) => weekDayMap[day]);
                    }
                }
                delete data.attendees; // Just for the demo
                if (all) {
                    data.id = primaryId;
                }
                return data;
            },
        }
    ), EVENT_UPDATED, 'data.updateExternalEvent.event')
        .catch((error) => {
            if (!checkAndHandleMissingError(error, false)) {
                throw error;
            }
            const client = baseApolloClient();
            client.refetchQueries({ include: [EXTERNAL_CALENDARS, EXTERNAL_EVENTS] });
            return false;
        });
}

export function updateEvent(form, event, allFuture = false) {
    if (form.sourceId) {
        return updateExternalEvent(form, event, allFuture);
    }
    return updateInternalEvent(form, allFuture);
}

export function duplicateEvent(event, records) {
    const client = baseApolloClient();
    return client.mutate({
        mutation: DUPLICATE_EVENT,
        variables: {
            input: {
                id: event.id,
                ...records,
            },
        },
        refetchQueries: getCachedOperationNames([
            EVENTS,
        ], client),
    }).then((result) => {
        eventBus.dispatch(EVENT_CREATED, result.data.duplicateEvent.event);
        return result;
    });
}

export function createInternalEvent(form) {
    return dispatchPromise(form.graphql(CREATE_EVENT, {
        formatData(dataWithTypename) {
            const data = removeTypename(dataWithTypename);
            if (data.description) {
                data.description = JSON.stringify(data.description);
            }
            delete data.attendees; // Just for the demo
            if (_.has(data, 'recurrence')) {
                if (!data.recurrence?.frequency) {
                    data.recurrence = null;
                } else if (data.recurrence.byDay?.length) {
                    data.recurrence.byDay = data.recurrence.byDay.map((index) => weekDayMap[index]);
                }
            }
            return {
                ..._.omit(data, ['sourceId']),
                associations: data.associations.map((item) => item?.id || item),
                assigneeGroups: (data.assigneeGroups || []).map((assigneeGroup) => ({
                    groupId: assigneeGroup.groupId,
                    assignees: _.map(assigneeGroup.assignees, 'id'),
                })),
            };
        },
        refetchQueries: [
            EVENTS,
        ],
    }), EVENT_CREATED, 'data.createEvent.event');
}

export function createExternalEvent(form) {
    return dispatchPromise(form.graphql(CREATE_EXTERNAL_EVENT, {
        formatData(dataWitTypename) {
            const data = removeTypename(dataWitTypename);
            if (data.description) {
                data.description = JSON.stringify(data.description);
            }
            return {
                ..._.omit(data, ['markers']),
                // tags: data.tags.map((tag) => tag?.id || tag),
                associations: data.associations.map((item) => item?.id || item),
            };
        },
        refetchQueries: [
            EXTERNAL_EVENTS,
        ],
    }), EVENT_CREATED, 'data.createExternalEvent.event')
        .catch((error) => {
            if (!checkAndHandleMissingError(error, false)) {
                throw error;
            }
            const client = baseApolloClient();
            client.refetchQueries({ include: [EXTERNAL_CALENDARS] });
            return false;
        });
}

export function createEvent(form) {
    if (form.sourceId) {
        return createExternalEvent(form);
    }
    return createInternalEvent(form);
}

export function deleteInternalEvent(event, allFuture = false) {
    const client = baseApolloClient();
    return client.mutate({
        mutation: DELETE_EVENT,
        variables: {
            input: {
                id: event.id,
                thisAndFuture: allFuture,
            },
        },
        refetchQueries: getCachedOperationNames([
            EVENTS,
        ], client),
    }).then((result) => {
        eventBus.dispatch(EVENT_DELETED, event);
        return result;
    });
}

export function deleteExternalEvent(event, all = false) {
    const client = baseApolloClient();
    return client.mutate({
        mutation: DELETE_EXTERNAL_EVENT,
        variables: {
            input: {
                sourceId: event.account.id,
                calendarId: event.calendar.id,
                id: all ? event.primaryId : event.id,
            },
        },
        refetchQueries: getCachedOperationNames([
            EXTERNAL_EVENTS,
        ], client),
    }).then((result) => {
        eventBus.dispatch(EVENT_DELETED, event);
        return result;
    }).catch((error) => {
        if (!checkAndHandleMissingError(error, false)) {
            throw error;
        }
        client.refetchQueries({ include: [EXTERNAL_CALENDARS, EXTERNAL_EVENTS] });
        return false;
    });
}

export function deleteEvent(event, allOrFuture = false) {
    if (event.account) {
        return deleteExternalEvent(event, allOrFuture);
    }
    return deleteInternalEvent(event, allOrFuture);
}

export function changeCalendar(event, calendarId) {
    const client = baseApolloClient();
    return client.mutate({
        mutation: UPDATE_EVENT,
        variables: {
            input: {
                id: event.id,
                calendarId,
            },
        },
        refetchQueries: getCachedOperationNames([
            EVENTS,
        ], client),
    });
}
