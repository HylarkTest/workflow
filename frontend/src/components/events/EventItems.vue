<template>
    <div class="o-event-items">
        <component
            :is="eventsComponent"
            :events="events"
            :currentView="currentView"
            v-bind="$attrs"
            @openItem="$emit('openItem', $event)"
            @selectEvent="$emit('openItem', $event)"
        >
            <template
                #noContentSlot
            >
                <slot
                    name="noContentSlot"
                >
                </slot>
            </template>
        </component>
    </div>
</template>

<script>

import EventsCalendar from '@/components/events/EventsCalendar.vue';
import EventList from '@/components/events/EventList.vue';

const viewComponents = {
    CALENDAR: 'EventsCalendar',
    LINE: 'EventList',

};

export default {
    name: 'EventItems',
    components: {
        EventsCalendar,
        EventList,
    },
    mixins: [
    ],
    props: {
        events: {
            type: [Array, null],
            required: true,
        },
        currentView: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'openItem',
    ],
    data() {
        return {

        };
    },
    computed: {
        viewType() {
            return this.currentView.viewType;
        },
        eventsComponent() {
            return viewComponents[this.viewType];
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.o-event-items {

} */

</style>
