<template>
    <FeatureFormBase
        v-model:form="form"
        v-model:formAssociations="form.associations"
        v-model:formMarkers="form.markers"
        v-model:formListId="form.calendarId"
        v-model:formAssigneeGroups="form.assigneeGroups"
        class="o-calendar-form"
        v-bind="baseProps"
        :changeListFunction="changeCalendar"
        :integrationAccountId="integrationAccountId"
        @saveItem="saveItem(true)"
        @deleteItem="deleteEventOptions"
        @updateSourceId="updateSourceId"
    >
        <div class="mb-6">
            <label class="header-form mb-3">
                {{ $t('features.events.labels.when') }}*
            </label>

            <EventDatePicker
                v-model:isAllDay="form.isAllDay"
                v-model:startDate="form.startAt"
                v-model:endDate="form.endAt"
                :timezone="form.timezone"
                :error="form.errors().getFirst('startAt') || form.errors().getFirst('endAt')"
            >
            </EventDatePicker>
        </div>

        <div
            v-if="!formIsAllDay"
            class="mb-6"
        >
            <label class="header-form mb-2 block">
                {{ $t('features.events.labels.timezone') }}*
            </label>

            <TimezoneDropdown
                v-model="formTimezone"
                :error="form.errors().getFirst('timezone')"
            >
            </TimezoneDropdown>

            <div
                v-if="isDifferentTimezone"
                class="bg-secondary-100 rounded-xl mt-2 py-2 px-4"
            >
                <p class="mb-2 tight">
                    {{ $t('features.events.differentTimezone') }}
                </p>

                <div>
                    <p class="font-medium">
                        {{ $t('features.events.currentTimezone') }}:
                        <span class="font-semibold">({{ timezone }})</span>:
                    </p>
                    <p>
                        <template v-if="isSingleDay">
                            {{ formStartAtTimeFormatted }} - {{ formEndAtTimeFormatted }} {{ formEndAtDateFormatted }}
                        </template>

                        <template v-else>
                            {{ formStartAtFullFormatted }} - {{ formEndAtFullFormatted }}
                        </template>
                    </p>
                </div>
            </div>
        </div>

        <div
            class="mb-6"
        >
            <label class="header-form block">
                {{ $t('features.events.labels.repeat') }}
            </label>

            <RecurrenceForm
                v-model:recurrence="form.recurrence"
                :error="form.errors().getFirst('recurrence.interval')"
            >
            </RecurrenceForm>
        </div>

        <div
            v-if="canEditLocation"
            class="mb-6"
        >
            <label
                v-t="'labels.location'"
                class="header-form block"
            >
            </label>

            <InputBox
                formField="location"
                bgColor="gray"
                :placeholder="$t('features.events.form.placeholders.location')"
            >
            </InputBox>
        </div>

        <EventRepeatConfirm
            v-if="isModalOpen"
            :action="recurrenceModalAction"
            :external="event?.isExternalItem()"
            @closeModal="closeModal"
            @cancelAction="closeModal"
            @triggerAction="completeAction"
        >
        </EventRepeatConfirm>
    </FeatureFormBase>
</template>

<script>

import EventDatePicker from './EventDatePicker.vue';
import RecurrenceForm from '@/components/assets/RecurrenceForm.vue';
import TimezoneDropdown from '@/components/time/TimezoneDropdown.vue';
// import DeleteButton from '@/components/buttons/DeleteButton.vue';

import interactsWithFeatureForms from '@/vue-mixins/features/interactsWithFeatureForms.js';
import interactsWithEventRecurrence from '@/vue-mixins/events/interactsWithEventRecurrence.js';

import EXTERNAL_EVENT from '@/graphql/calendar/queries/ExternalEvent.gql';
import EVENT from '@/graphql/calendar/queries/Event.gql';

import {
    changeCalendar,
    createEvent,
    createEventFromObject,
    updateEvent,
} from '@/core/repositories/eventRepository.js';

import Calendar from '@/core/models/Calendar.js';

import { getFirstKey } from '@/core/utils.js';
import { timezone } from '@/core/repositories/preferencesRepository.js';
import { getEventFullDateWithTz } from '@/core/helpers/dateHelpers.js';

export default {
    name: 'EventForm',
    components: {
        RecurrenceForm,
        TimezoneDropdown,
        EventDatePicker,
        // DeleteButton,
    },
    mixins: [
        interactsWithFeatureForms,
        interactsWithEventRecurrence,
    ],
    props: {
        calendar: {
            type: [Calendar, null],
            default: null,
        },
        event: {
            type: [Object, null],
            default: null,
        },
        time: {
            type: [Object, null],
            default: null,
        },
    },
    emits: [
        'closeModal',
    ],
    apollo: {
        fullEvent: {
            query() {
                return this.isExternal ? EXTERNAL_EVENT : EVENT;
            },
            skip() {
                return !this.event?.id;
            },
            variables() {
                if (this.isExternal) {
                    return {
                        sourceId: this.event.account.id,
                        calendarId: this.event.calendar.id,
                        id: this.event.id,
                    };
                }
                return { id: this.event.id };
            },
            update: (data) => createEventFromObject(getFirstKey(data)),
        },
    },
    data() {
        return {
            timezone,
            changedEndAt: false,
            listKey: 'calendarId',
            listObjKey: 'calendar',
            featureType: 'EVENTS',
            form: this.$apolloForm(() => {
                const isExternal = this.calendar?.isExternalList();

                const startAt = this.event
                    ? this.startDate(this.event)
                    : this.backupStartDate();

                const endAt = this.event
                    ? this.endDate(this.event)
                    : this.backupEndDate(this.backupStartDate());

                const data = {
                    name: this.event?.name || '',
                    description: this.event?.description || '',
                    startAt,
                    endAt,
                    isAllDay: this.event?.isAllDay || false,
                    timezone: this.event?.timezone || null,
                    location: this.event?.location || '',
                    recurrence: this.event?.recurrence || null,
                };

                if (this.calendar?.id) {
                    data.calendarId = this.calendar.id;
                }
                if (this.isNew) {
                    data.associations = this.defaultAssociations || [];
                    data.sourceId = this.calendar?.account?.id || null;

                    if (!_.has(data, 'calendarId')) {
                        data.calendarId = null;
                    }
                    if (!isExternal) {
                        data.markers = [];
                        data.assigneeGroups = [];
                    }
                } else {
                    data.id = this.event.id;

                    if (this.event.account) {
                        data.sourceId = this.event.account.id;
                    }
                }

                return data;
            }),
        };
    },
    computed: {
        // passedCalendarSpaceId() {
        //     return this.calendar?.space?.id;
        // },
        // calendarSpaceId() {
        //     return this.savedItem?.calendar.space?.id;
        // },

        // General
        // Used in mixin
        savedItem() {
            return this.fullEvent;
        },

        // Form info
        hiddenSections() {
            const sections = [];
            if (this.isExternal) {
                sections.push('MARKERS');
            }
            return sections;
        },
        cantModifySections() {
            const sections = [];
            if (this.isExistingExternal) {
                sections.push('LIST');
            }
            return sections;
        },
        canEditLocation() {
            return !this.isExternal;
        },

        // Integrations
        isExternal() {
            return this.isCalendarExternal || this.isEventExternal;
        },
        isCalendarExternal() {
            return this.calendar?.isExternalList();
        },
        isEventExternal() {
            return this.fullEvent?.isExternalItem();
        },
        integrationAccountId() {
            return this.calendar?.account?.id;
        },
        isExistingExternal() {
            return this.isExternal && !this.isNew;
        },
        shouldSkipListQuery() {
            return this.isExistingExternal;
        },

        // Timezone
        isDifferentTimezone() {
            return this.formTimezone !== this.timezone;
        },
        formTimezone: {
            // For easier watch
            get() {
                return this.form.timezone;
            },
            set(newTimezone) {
                this.form.timezone = newTimezone;
            },
        },

        // Recurrence
        hasEventRecurrence() {
            return !!this.recurrenceCheck(this.fullEvent);
        },

        // Time
        formIsAllDay() {
            return this.form.isAllDay;
        },
        formStartAt() {
            return this.form.startAt;
        },
        formEndAt() {
            return this.form.endAt;
        },
        formStartAtDateObject() {
            // Because the form date does not contain any timezone information,
            // we need to add the timezone to it so the helper functions know
            // what to do with it.
            return this.$dayjs.tz(this.formStartAt, 'utc');
        },
        formEndAtDateObject() {
            return this.$dayjs.tz(this.formEndAt, 'utc');
        },
        formStartAtDateWithTz() {
            return getEventFullDateWithTz(this.formStartAtDateObject, this.formIsAllDay);
        },
        formEndAtDateWithTz() {
            return getEventFullDateWithTz(this.formEndAtDateObject, this.formIsAllDay);
        },
        formStartAtTimeFormatted() {
            return utils.formattedTime(this.formStartAtDateObject);
        },
        formEndAtTimeFormatted() {
            return utils.formattedTime(this.formEndAtDateObject);
        },
        formStartAtDateFormatted() {
            return this.formStartAtDateWithTz.format('ll');
        },
        formEndAtDateFormatted() {
            return this.formEndAtDateWithTz.format('ll');
        },
        formStartAtDateComparison() {
            return this.formStartAtDateWithTz.format('YYY-MM-DD');
        },
        formEndAtDateComparison() {
            return this.formEndAtDateWithTz.format('YYY-MM-DD');
        },
        formStartAtFullFormatted() {
            return `${this.formStartAtTimeFormatted} ${this.formStartAtDateFormatted}`;
        },
        formEndAtFullFormatted() {
            return `${this.formEndAtTimeFormatted} ${this.formEndAtDateFormatted}`;
        },
        isSingleDay() {
            return this.formStartAtDateComparison === this.formEndAtDateComparison;
        },
    },
    methods: {
        startDate(event) {
            const date = this.$dayjs.tz(event.date, 'utc');
            return date.format('YYYY-MM-DD HH:mm:ss');
        },
        endDate(event) {
            const date = this.$dayjs.tz(event.end, 'utc');
            return date.format('YYYY-MM-DD HH:mm:ss');
        },
        backupStartDate() {
            let date;
            if (this.time) {
                date = this.$dayjs.tz(this.time, timezone.value).tz('utc');
            } else {
                date = this.$dayjs.utc();
            }
            return date.format('YYYY-MM-DD HH:00:00');
        },
        backupEndDate(startDate) {
            const date = this.$dayjs(startDate).add(1, 'hours');
            return date.format('YYYY-MM-DD HH:00:00');
        },
        deleteEvent(affectedEventsKey) {
            // Need to send the the instance id as well as associations
            const event = {
                ...this.savedItem,
                id: this.event.id,
            };
            this.deleteEventIncludingRepeat(event, affectedEventsKey);
        },
        saveEvent(affectedEventsKey) {
            this.saveEventIncludingRepeat(this.form, this.savedItem, affectedEventsKey);
            this.$emit('closeModal');
        },
        saveItem() {
            if (this.isNew) {
                this.createEvent();
            } else {
                this.saveEventOptions();
            }
        },
        async createEvent() {
            this.processing = true;
            try {
                await this.createFunction(this.form);
                this.$emit('closeModal');
            } finally {
                this.processing = false;
            }
        },
    },
    watch: {
        'form.timezone': function onTimezone(newTz, oldTz) {
            // The time in the form is in UTC, if the timezone changes,
            // the time visible to the user should not change, so the
            // UTC time needs to change
            const oldTimezone = oldTz || timezone.value;
            this.form.startAt = this.$dayjs.tz(this.form.startAt, newTz)
                .tz(oldTimezone).format('YYYY-MM-DD HH:mm:ss');
            this.form.endAt = this.$dayjs.tz(this.form.endAt, newTz)
                .tz(oldTimezone).format('YYYY-MM-DD HH:mm:ss');
        },
        fullEvent(event) {
            // Because the recurrence is not there for all events depending
            // on whether it's a later one in the recurrence
            if (event) {
                this.form.recurrence = event.recurrence;
            }
        },
        formStartAt(newStartDate) {
            // When the start date changes and is after the end date,
            // end date needs to update
            const formatted = this.$dayjs(newStartDate);
            const endIsBefore = this.$dayjs(this.formEndAt).isBefore(formatted);
            if (endIsBefore || !this.changedEndAt) {
                this.form.endAt = this.backupEndDate(newStartDate);
            }
        },
        formEndAt() {
            // Mark that the end date was manually changed by the user
            // and is not the default
            this.changedEndAt = true;
        },
    },
    created() {
        this.changeCalendar = changeCalendar;
        this.createFunction = createEvent;
        this.updateFunction = updateEvent;

        if (!this.form.timezone) {
            this.form.timezone = timezone.value;
        }
        if (!this.isNew) {
            // If the event is not new, obviously the user set a manual
            // end date (or was fine with the default)
            this.changedEndAt = true;
        }
    },
};
</script>

<style>

/*.o-event-form {
}*/

</style>
