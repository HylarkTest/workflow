import UPDATE_CALENDAR from '@/graphql/calendar/mutations/UpdateCalendar.gql';
import UPDATE_EXTERNAL_CALENDAR from '@/graphql/calendar/mutations/UpdateExternalCalendar.gql';
import DELETE_CALENDAR from '@/graphql/calendar/mutations/DeleteCalendar.gql';
import DELETE_EXTERNAL_CALENDAR from '@/graphql/calendar/mutations/DeleteExternalCalendar.gql';
import EXTERNAL_CALENDARS from '@/graphql/calendar/queries/ExternalCalendars.gql';
import { instantiate } from '@/core/utils.js';
import CREATE_CALENDAR from '@/graphql/calendar/mutations/CreateCalendar.gql';
import CREATE_EXTERNAL_CALENDAR from '@/graphql/calendar/mutations/CreateExternalCalendar.gql';
import Calendar from '@/core/models/Calendar.js';
import MOVE_CALENDAR from '@/graphql/calendar/mutations/MoveCalendar.gql';
import CALENDARS from '@/graphql/calendar/queries/Calendars.gql';
import {
    addToQueryOffsetCallback, getCachedOperationNames,
} from '@/core/helpers/apolloHelpers.js';
import {
    createList,
    deleteList,
    initializeLists, moveList,
    updateList,
} from '@/core/repositories/listRepositoryHelpers.js';
import { baseApolloClient } from '@/core/repositories/baseRepository.js';
import { checkAndHandleMissingError } from '@/http/exceptionHandler.js';

export function createCalendarFromObject(obj) {
    return instantiate(obj, Calendar);
}

export function initializeCalendars(data) {
    if (_.has(data, 'externalCalendars')) {
        return {
            ...data.externalCalendars,
            data: data.externalCalendars.data.map((node) => createCalendarFromObject(node)),
        };
    }
    return initializeLists(data, createCalendarFromObject);
}

export function createInternalCalendar(form) {
    return createList(form, CREATE_CALENDAR, CALENDARS, createCalendarFromObject);
}

export function createExternalCalendar(form) {
    return form.graphql(
        CREATE_EXTERNAL_CALENDAR,
        {
            formatData(data) {
                return _.omit(data, 'color');
            },
            update: addToQueryOffsetCallback(
                { query: EXTERNAL_CALENDARS, variables: { sourceId: form.sourceId } },
                'createExternalCalendar.calendar',
                'externalCalendars'
            ),
        }
    ).then((response) => response.data.createExternalCalendar.calendar);
}

export function createCalendar(form, space) {
    if (form.sourceId) {
        return createExternalCalendar(form);
    }
    return createInternalCalendar(form, space);
}

export function updateInternalCalendar(form, calendar) {
    return updateList(form, calendar, UPDATE_CALENDAR);
}

export function updateExternalCalendar(form) {
    return form.graphql(
        UPDATE_EXTERNAL_CALENDAR
    ).catch((error) => {
        if (!checkAndHandleMissingError(error, false)) {
            throw error;
        }
        const client = baseApolloClient();
        client.refetchQueries({ include: [EXTERNAL_CALENDARS] });
        return false;
    });
}

export function updateCalendar(form, calendar) {
    if (form.sourceId) {
        return updateExternalCalendar(form, calendar);
    }
    return updateInternalCalendar(form, calendar);
}

export function deleteInternalCalendar(calendar) {
    return deleteList(calendar, DELETE_CALENDAR, CALENDARS);
}

export function deleteExternalCalendar(calendar) {
    const client = baseApolloClient();
    return client.mutate({
        mutation: DELETE_EXTERNAL_CALENDAR,
        variables: {
            input: { id: calendar.id, sourceId: calendar.account.id },
        },
        refetchQueries: getCachedOperationNames([EXTERNAL_CALENDARS], client),
        // update: removeNodeFromQueryConnectionCallback(
        //     { query: EXTERNAL_CALENDARS, variables: { sourceId: calendar.account.id } },
        //     'externalCalendars',
        //     calendar.id
        // ),
    }).catch((error) => {
        if (!checkAndHandleMissingError(error, false)) {
            throw error;
        }
        client.refetchQueries({ include: [EXTERNAL_CALENDARS] });
        return false;
    });
}

export function deleteCalendar(calendar, page) {
    if (calendar.account) {
        return deleteExternalCalendar(calendar);
    }
    return deleteInternalCalendar(calendar, page);
}

export function moveCalendar(calendar, previousCalendar = null) {
    return moveList(calendar, previousCalendar, MOVE_CALENDAR, CALENDARS);
}
