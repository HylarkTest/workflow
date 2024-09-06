// Requires method called inContextVariables()

import interactsWithIntegratedData from '@/vue-mixins/interactsWithIntegratedData.js';

import {
    createCalendar,
    createCalendarFromObject,
    initializeCalendars,
    deleteCalendar,
    updateCalendar,
    moveCalendar,
} from '@/core/repositories/calendarRepository.js';

import { moveEventToList } from '@/core/repositories/eventRepository.js';

import CALENDARS from '@/graphql/calendar/queries/Calendars.gql';
import EXTERNAL_CALENDARS from '@/graphql/calendar/queries/ExternalCalendars.gql';
import EVENTS from '@/graphql/calendar/queries/Events.gql';

import CALENDAR_CREATED from '@/graphql/calendar/subscriptions/CalendarCreated.gql';
import CALENDAR_UPDATED from '@/graphql/calendar/subscriptions/CalendarUpdated.gql';
import CALENDAR_DELETED from '@/graphql/calendar/subscriptions/CalendarDeleted.gql';
import CALENDAR_RESTORED from '@/graphql/calendar/subscriptions/CalendarRestored.gql';
import CALENDAR_MOVED from '@/graphql/calendar/subscriptions/CalendarMoved.gql';
import { subscribeToUpdates } from '@/core/helpers/apolloHelpers.js';

const subscriptions = [CALENDAR_CREATED, CALENDAR_UPDATED, CALENDAR_DELETED, CALENDAR_RESTORED, CALENDAR_MOVED];

export default {
    mixins: [
        interactsWithIntegratedData,
    ],
    apollo: {
        calendars: {
            query: CALENDARS,
            update: initializeCalendars,
            variables() {
                return this.contextVariables();
            },
            fetchPolicy: 'cache-first',
        },
    },
    data() {
        return {
            scope: 'CALENDAR',
        };
    },
    computed: {
        isLoading() {
            return this.isLoadingLists;
        },
        isLoadingLists() {
            // Ideally we don't want to have to wait for integrations to load
            // as they may take a while, but in order for the router to work
            // if they are following a link to an integrated list, then we need
            // to wait. If setDisplayedList can be updated we may be able to
            // remove this.
            return this.$isLoadingQueriesFirstTime(['calendars'])
                || this.isLoadingInitialIntegrations;
        },
        sourceLists() {
            return this.calendars?.map((calendar) => {
                return {
                    ...calendar.space,
                    lists: calendar.lists || [],
                };
            });
        },
        sources() {
            return {
                spaces: this.sourceLists,
                integrations: (this.integrationsForScope || []).map((integration) => {
                    return {
                        name: integration.accountName,
                        id: integration.id,
                        provider: integration.provider,
                        renewalUrl: this.renewals[integration.id] || null,
                        lists: this.integrationLists[integration.id]?.data || [],
                    };
                }),
            };
        },
    },
    methods: {
    },
    watch: {
        integrationsForScope() {
            this.createIntegrationSmartQueries(
                EXTERNAL_CALENDARS,
                initializeCalendars
            );
        },
    },
    created() {
        this.deleteListFunction = deleteCalendar;
        this.createListFromObjectFunction = createCalendarFromObject;
        this.updateListFunction = updateCalendar;
        this.createListFunction = createCalendar;
        this.moveListFunction = moveCalendar;
        this.moveItemToListFunction = moveEventToList;

        const client = this.$apollo.provider.defaultClient;
        const refetchableQueries = [CALENDARS, EVENTS];
        this.subscriptionCallback = subscribeToUpdates(client, subscriptions, refetchableQueries, [CALENDAR_UPDATED]);
    },
    unmounted() {
        this.subscriptionCallback();
    },
};
