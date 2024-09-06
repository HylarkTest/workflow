<template>
    <div class="o-week-day-range">
        <AllDay
            :range="range"
            :events="allDayEvents"
            @selectEvent="$emit('selectEvent', $event)"
            @newEvent="$emit('newEvent', $event)"
        >
        </AllDay>

        <div class="o-week-day-range__main">
            <HoursColumn
                class="o-calendar-weekly__hours"
                :showCurrentTime="showCurrentTime"
            >
            </HoursColumn>

            <div class="flex flex-1 ml-8">
                <DailyColumn
                    v-for="(day, index) in range"
                    ref="columns"
                    :key="day"
                    :class="displayClass"
                    :times="hoursAndMinutes"
                    :events="daysEvents(day)"
                    :day="day"
                    @click="emitAddEvent(index, $event)"
                    @selectEvent="$emit('selectEvent', $event)"
                >
                </DailyColumn>
            </div>
        </div>
    </div>
</template>

<script>

import _ from 'lodash';
import DailyColumn from './DailyColumn.vue';
import HoursColumn from './HoursColumn.vue';
import AllDay from './AllDay.vue';

import interactsWithHours from '@/vue-mixins/calendars/interactsWithHours.js';
import { eventPositioning } from '@/core/helpers/dateHelpers.js';

const minutesRange = _.range(0, 60, 5);

export default {
    name: 'WeekDayRange',
    components: {
        DailyColumn,
        HoursColumn,
        AllDay,
    },
    mixins: [
        interactsWithHours,
    ],
    props: {
        range: {
            type: Array,
            required: true,
        },
        showCurrentTime: Boolean,
        events: {
            type: [Array, null],
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
        allDayEvents() {
            return this.events.filter((event) => event.isAllDay);
        },
        timedEvents() {
            return this.events.filter((event) => !event.isAllDay);
        },
        eventPositioning() {
            return eventPositioning(this.timedEvents, (eventVisual) => {
                const splitVisuals = [];
                let visual = eventVisual;

                // Split up the event into multiple events for each week
                // that it spans.
                while (visual.start.isBefore(visual.end)) {
                    const visualStart = visual.start;
                    let visualEnd = visualStart.clone().endOf('day');

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
                        start: visualEnd.clone().add(1, 'minute'),
                    };
                }

                return splitVisuals;
            }, 'minutes');
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
        minutes() {
            return minutesRange.map((minute) => {
                return _.padStart(minute, 2, '0');
            });
        },
        hoursAndMinutes() {
            return _(this.hours).flatMap((hour) => {
                return this.minutes.map((minute) => {
                    return `${hour}:${minute}`;
                });
            }).value();
        },
        displayClass() {
            if (this.range.length === 1) {
                return 'w-full';
            }
            return 'o-events-calendar__seventh';
        },
    },
    methods: {
        daysEvents(day) {
            return this.groupedEvents[day.format('YYYY-MM-DD')] || [];
        },
        emitAddEvent(index, event) {
            // Get the column element that was clicked.
            const el = this.$refs.columns[index].$el;
            // Get the box info.
            const rect = el.getBoundingClientRect();
            // Calculate how far down the click was as a fraction of the height of the element.
            const fractionClicked = (event.clientY - rect.top) / rect.height;
            const hourClicked = Math.floor(fractionClicked * 24);
            const day = this.range[index].startOf('day').add(hourClicked, 'hours');
            this.$emit('newEvent', day);
        },
    },
    created() {

    },
};
</script>

<style scoped>

.o-week-day-range {
    @apply
        flex
        flex-col
        min-h-0
    ;

    &__main {
        @apply
            flex
            flex-1
            mt-3
            overflow-auto
            relative
        ;
    }
}

</style>
