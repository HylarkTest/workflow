<template>
    <div
        class="o-full-day relative"
    >
        <div
            v-for="visual in events"
            :key="visual.event.id"
            :ref="setRef(visual.event.id)"
            class="mx-0.5 absolute z-over"
            :style="eventStyle(visual)"
        >
            <DailyEvent
                :event="visual.event"
                :day="day"
                @click.stop
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
    name: 'FullDay',
    components: {
        DailyEvent,
    },
    mixins: [
        interactsWithAutoScroll,
        interactsWithEventBus,
    ],
    props: {
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
    },
    methods: {
        handleEventChange(event) {
            this.setScrollTarget(event.id);
        },
        eventStyle(visual) {
            const top = visual.column * 38;
            return {
                height: '36px',
                width: `calc(${visual.length * 100}% - 2px)`,
                top: `${top}px`,
            };
        },
    },
};
</script>

<style scoped>

/*.o-full-day {

} */

</style>
