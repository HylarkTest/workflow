<template>
    <div class="o-daily-column relative">
        <div
            v-for="visual in eventsWithStartMinutes"
            :key="visual.event.id"
            :ref="setRef(visual.event.id)"
            class="o-daily-column__event absolute"
            :style="eventStyle(visual)"
        >
            <DailyEvent
                class="w-full z-over"
                :event="visual.event"
                :cellDateObj="day"
                @selectEvent="$emit('selectEvent', $event)"
            >
            </DailyEvent>
        </div>
    </div>
</template>

<script>

import DailyEvent from './DailyEvent.vue';

import {
    EVENT_CREATED,
    EVENT_UPDATED,
} from '@/core/repositories/eventRepository.js';

import interactsWithAutoScroll from '@/vue-mixins/interactsWithAutoScroll.js';
import interactsWithEventBus from '@/vue-mixins/interactsWithEventBus.js';

export default {
    name: 'DailyColumn',
    components: {
        DailyEvent,
    },
    mixins: [
        interactsWithAutoScroll,
        interactsWithEventBus,
    ],
    props: {
        showHours: Boolean,
        times: {
            type: Array,
            required: true,
        },
        events: {
            type: Array,
            default: () => ([]),
        },
        day: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'selectEvent',
    ],
    data() {
        return {
            listeners: {
                handleEventChange: [EVENT_CREATED, EVENT_UPDATED],
            },
        };
    },
    computed: {
        dayFormatted() {
            return this.day.format('YYYY-MM-DD');
        },
        eventsWithStartMinutes() {
            return this.events.map((visual) => {
                const event = visual.event;
                const start = utils.dateWithTz(event.date);

                const hasStartToday = start.format('YYYY-MM-DD') === this.dayFormatted;

                let todayStart;

                if (hasStartToday) {
                    todayStart = utils.dateWithTz(start);
                } else {
                    const date = `${this.dayFormatted} 0:00:00`;
                    todayStart = this.$dayjs(date);
                }

                if (todayStart.minutes % 5) {
                    todayStart = todayStart.subtract(todayStart.minutes % 5, 'minutes');
                }

                const minutes = todayStart.hour() * 60 + todayStart.minute();

                return {
                    ...visual,
                    startMinutes: minutes,
                };
            });
        },
    },
    methods: {
        handleEventChange(event) {
            this.setScrollTarget(event.id);
        },
        eventStyle(visual) {
            const widthPercent = `${visual.width}%`;
            return {
                width: widthPercent,
                minWidth: widthPercent,
                maxWidth: widthPercent,
                left: `${visual.left}%`,
                top: `${visual.startMinutes}px`,
            };
        },
        isBetween(start, end) {
            return this.$dayjs(this.day).isBetween(start, end, null, '[]');
        },
    },
};
</script>

<style scoped>

.o-daily-column {
    margin-top: 7px;

    &__hour {
        height: 5px;

        @apply
            flex
        ;
    }

    &__event {
        @apply
            flex
            flex-1
            px-0.5
        ;
    }
}

</style>
