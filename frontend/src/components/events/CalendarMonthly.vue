<template>
    <div class="o-calendar-monthly">
        <Teleport
            v-if="teleportRef"
            :to="teleportRef"
        >
            <div class="o-events-calendar__days">
                <div
                    v-for="day in weekdays"
                    :key="day"
                    class="o-events-calendar__seventh o-events-calendar__weekday"
                >
                    <span class="sm:hidden text-center">
                        {{ $t('common.dates.days.' + day + '.one') }}
                    </span>

                    <span class="hidden text-center sm:block md:hidden">
                        {{ $t('common.dates.days.' + day + '.short') }}
                    </span>

                    <span class="hidden text-center md:block">
                        {{ $t('common.dates.days.' + day + '.full') }}
                    </span>
                </div>
            </div>
        </Teleport>
        <div
            class="o-calendar-monthly__cells"
        >
            <MonthlyCell
                v-for="(day, index) in calendarRange"
                :key="day"
                class="o-events-calendar__seventh o-calendar-monthly__day"
                :class="borderClass(index)"
                :day="day"
                :viewedMonth="viewedMonth"
                :daysEvents="daysEvents(day)"
                :weekStart="weekStart"
                :dayIndex="index % 7"
                @selectEvent="$emit('selectEvent', $event)"
                @newEvent="$emit('newEvent', $event)"
                @click="$emit('newEvent', day)"
            >
            </MonthlyCell>
        </div>
    </div>
</template>

<script>

import MonthlyCell from './MonthlyCell.vue';

import interactsWithWeekdays from '@/vue-mixins/calendars/interactsWithWeekdays.js';
import providesCalendarRange from '@/vue-mixins/calendars/providesCalendarRange.js';
import { eventPositioning } from '@/core/helpers/dateHelpers.js';
import { formatDateTime } from '@/core/dateTimeHelpers.js';

export default {
    name: 'CalendarMonthly',
    components: {
        MonthlyCell,
    },
    mixins: [
        interactsWithWeekdays,
        providesCalendarRange,
    ],
    props: {
        viewedMonth: {
            type: Number,
            required: true,
        },
        viewedYear: {
            type: Number,
            required: true,
        },
        events: {
            type: [Array, null],
            default: null,
        },
        teleportRef: {
            type: [Object, null],
            default: null,
        },
    },
    emits: [
        'selectEvent',
        'newEvent',
    ],
    data() {
        return {
        };
    },
    computed: {
        eventPositioning() {
            return eventPositioning(this.events, (eventVisual) => {
                const splitVisuals = [];
                let visual = eventVisual;

                // Split up the event into multiple events for each week
                // that it spans.
                while (visual.start.isSameOrBefore(visual.end)) {
                    const visualStart = visual.start;

                    const startDay = formatDateTime(visual.start, 'DD');
                    const visualStartWeekDay = ((startDay + this.firstDayInMonth) % 7) || 7;

                    const daysUntilEndOfWeek = 7 - visualStartWeekDay;

                    let visualEnd = visualStart.clone().add(daysUntilEndOfWeek, 'days');

                    if (visualEnd.isAfter(visual.end)) {
                        visualEnd = visual.end;
                    }

                    splitVisuals.push({
                        ...visual,
                        start: visualStart,
                        end: visualEnd,
                    });

                    visual = {
                        ...visual,
                        start: visualEnd.clone().add(1, 'day'),
                    };
                }

                return splitVisuals;
            }, 'days');
        },
        groupedEvents() {
            const groupedEvents = {};
            _.forEach(this.eventPositioning, (visual) => {
                const start = this.$dayjs(visual.start.toString(), 'YYYYMMDD');
                const date = start.format('YYYY-MM-DD');
                if (!groupedEvents[date]) {
                    groupedEvents[date] = [];
                }
                groupedEvents[date].push(visual);
            });

            return groupedEvents;
        },
    },
    methods: {
        borderClass(index) {
            let classes = '';
            if (index > 6) {
                classes += classes.concat(' border-t border-primary-200');
            }
            const remainder = index % 7;
            if (remainder !== 0) {
                classes += classes.concat(' border-l border-primary-200');
            }
            return classes;
        },
        daysEvents(day) {
            return this.groupedEvents[day.format('YYYY-MM-DD')] || [];
        },
    },
    created() {

    },
};
</script>

<style scoped>

.o-calendar-monthly {
    @apply
        flex
        flex-col
        min-h-0
    ;

    &__cells {
        @apply
            border
            border-primary-200
            border-solid
            border-t-0
            flex
            flex-wrap
            rounded-b-xl
        ;
    }

    &__day {
        height:  130px;

        @apply
            border-solid
        ;
    }

}

</style>
