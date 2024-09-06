<template>
    <div class="o-all-day">
        <div class="text-cm-500 text-xs">
            All day
        </div>
        <div
            class="flex flex-1 ml-10"
            :style="{ height: maxEventHeight }"
        >
            <FullDay
                v-for="day in range"
                :key="day"
                :class="displayClass"
                :events="daysEvents(day)"
                :day="day"
                @click="$emit('newEvent', day)"
                @selectEvent="$emit('selectEvent', $event)"
            >

            </FullDay>
        </div>
    </div>
</template>

<script>

import FullDay from './FullDay.vue';
import { eventPositioning } from '@/core/helpers/dateHelpers.js';

export default {
    name: 'AllDay',
    components: {
        FullDay,
    },
    mixins: [
    ],
    props: {
        range: {
            type: Array,
            required: true,
        },
        events: {
            type: Array,
            default: () => ([]),
        },
    },
    emits: [
        'newEvent',
        'selectEvent',
    ],
    data() {
        return {

        };
    },
    computed: {
        displayClass() {
            if (this.range.length === 1) {
                return 'w-full';
            }
            return 'o-events-calendar__seventh';
        },
        eventsInRange() {
            const startOfRange = _.first(this.range);
            const endOfRange = _.last(this.range);
            return this.events.filter((event) => {
                return (startOfRange.isSameOrBefore(event.date, 'day')
                    && endOfRange.isSameOrAfter(event.date, 'day'))
                    || (startOfRange.isSameOrBefore(event.end, 'day')
                    && endOfRange.isSameOrAfter(event.end, 'day'))
                    || (startOfRange.isAfter(event.date, 'day')
                    && endOfRange.isBefore(event.end, 'day'));
            });
        },
        eventPositioning() {
            return eventPositioning(this.eventsInRange, (eventVisual) => {
                const startOfRange = _.first(this.range);
                const endOfRange = _.last(this.range);
                const start = eventVisual.start.isBefore(startOfRange) && eventVisual.end.isAfter(startOfRange)
                    ? startOfRange
                    : eventVisual.start.startOf('day');
                const end = eventVisual.end.isAfter(endOfRange) && eventVisual.start.isBefore(endOfRange)
                    ? endOfRange
                    : eventVisual.end.endOf('day');
                return [{
                    ...eventVisual,
                    start,
                    end,
                    length: end.diff(start, 'day') + 1,
                }];
            }, 'days');
        },
        groupedEvents() {
            const groupedEvents = {};
            _.forEach(this.eventPositioning, (visual) => {
                const start = utils.dateWithTz(this.$dayjs(visual.start.toString(), 'YYYYMMDD'));
                const date = start.format('YYYY-MM-DD');
                if (!groupedEvents[date]) {
                    groupedEvents[date] = [];
                }
                groupedEvents[date].push(visual);
            });

            return groupedEvents;
        },
        maxEventHeight() {
            return `${(_.max(_.map(this.eventPositioning, 'columnCount')) || 1) * 38}px`;
        },
    },
    methods: {
        daysEvents(day) {
            const date = day.format('YYYY-MM-DD');
            return this.groupedEvents[date] || [];
        },
    },
    created() {

    },
};
</script>

<style scoped>

.o-all-day {
    max-height: 120px;

    @apply
        border-b-2
        border-cm-300
        border-solid
        flex
        grow
        overflow-auto
        py-2
        shrink-0
    ;
}

</style>
