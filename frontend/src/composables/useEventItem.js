import {
    toRefs,
    reactive,
    computed,
    watch,
    getCurrentInstance,
} from 'vue';

import useDateTime from './useDateTime.js';

import {
    formatDateTime,
    getDateObject,
} from '@/core/dateTimeHelpers.js';

import { checkAndHandleMissingError } from '@/http/exceptionHandler.js';
import EXTERNAL_CALENDARS from '@/graphql/calendar/queries/ExternalCalendars.gql';
import EXTERNAL_EVENT from '@/graphql/calendar/queries/ExternalEvent.gql';
import EXTERNAL_EVENTS from '@/graphql/calendar/queries/ExternalEvents.gql';

export default (props, context) => {
    const {
        event,
        cellDateObj,
        actionProcessing = { value: false },
        deleteProcessing = { value: false },
    } = toRefs(props);

    const currentInstance = computed(() => getCurrentInstance());

    const root = currentInstance.value.root.ctx;
    const color = computed(() => event.value.calendar.color);
    const textColor = computed(() => root.extraColorDisplay(color.value));
    const bgColor = computed(() => root.extraColorDisplay(color.value, '100'));

    // create props for the event's starting DateTime conversion
    const startProp = reactive({ dateTime: event.value.date });
    watch(() => event.value.date, (value) => {
        startProp.dateTime = value;
    });
    const endProp = reactive({ dateTime: event.value.end });
    watch(() => event.value.end, (value) => {
        endProp.dateTime = value;
    });

    // call useDateTime twice, once for event.date and once for event.end
    const {
        modelValue: startDateTime,
        modelValueDate: startDate,
        modelValueTime: startTime,
        isAllDay: isStartAllDay,
    } = useDateTime(startProp);

    const {
        modelValue: endDateTime,
        modelValueDate: endDate,
        modelValueTime: endTime,
        isAllDay: isEndAllDay,
    } = useDateTime(endProp);

    const viewedCellDay = computed(() => cellDateObj.value?.format('YYYY-MM-DD'));
    const isSingleDay = computed(() => startDate.value === endDate.value);
    const isAllDay = computed(() => event.value.isAllDay || (isStartAllDay.value && isEndAllDay.value));

    const isFirstDay = computed(() => isSingleDay.value || (viewedCellDay.value === startDate.value));
    const isLastDay = computed(() => isSingleDay.value || (viewedCellDay.value === endDate.value));
    const isMiddleDay = computed(() => {
        const start = getDateObject(startDate.value);
        const end = getDateObject(startDate.value);
        return !isSingleDay.value && getDateObject(viewedCellDay.value).isBetween(start, end, 'day');
    });

    const timeFormat = utils.timeDayjsFormat();
    const startTimeFormatted = computed(() => formatDateTime(startDateTime.value, timeFormat));
    const endTimeFormatted = computed(() => formatDateTime(endDateTime.value, timeFormat));

    const startDayMonthFormatted = computed(() => formatDateTime(startDateTime.value, 'D MMM'));
    const endDayMonthFormatted = computed(() => formatDateTime(endDateTime.value, 'D MMM'));

    const actionProcessingClass = computed(() => ({ unclickable: actionProcessing.value }));
    const deleteProcessingClass = computed(() => ({ unclickable: deleteProcessing.value }));

    const selectEvent = () => {
        if (event.value && event.value.account) {
            const client = currentInstance.value.ctx.$apollo.getClient();

            client.mutate({
                mutation: EXTERNAL_EVENT,
                variables: {
                    sourceId: event.value.account.id,
                    calendarId: event.value.calendar.id,
                    id: event.value.id,
                },
            })
                .then(() => {
                    context.emit('selectEvent', event.value);
                })
                .catch((error) => {
                    if (!checkAndHandleMissingError(error, false)) {
                        throw error;
                    }
                    client.refetchQueries({ include: [EXTERNAL_CALENDARS, EXTERNAL_EVENTS] });
                });
        } else {
            context.emit('selectEvent', event.value);
        }
    };

    return {
        color,
        textColor,
        bgColor,

        startDateTime,
        startDate,
        startTime,
        endDateTime,
        endDate,
        endTime,

        viewedCellDay,
        isSingleDay,
        isAllDay,
        isFirstDay,
        isLastDay,
        isMiddleDay,

        startTimeFormatted,
        endTimeFormatted,

        startDayMonthFormatted,
        endDayMonthFormatted,

        actionProcessingClass,
        deleteProcessingClass,

        selectEvent,
    };
};
