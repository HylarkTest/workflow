<template>
    <Modal
        class="o-daily-modal"
        containerClass="p-8 w-600p"
        @closeModal="closeModal"
    >
        <CalendarDaily
            :viewedFullObject="viewedFullObject"
            :events="events"
            @selectEvent="selectEvent"
            @newEvent="newEvent"
        >
        </CalendarDaily>
    </Modal>
</template>

<script>

import CalendarDaily from '@/components/events/CalendarDaily.vue';

export default {
    name: 'DailyModal',
    components: {
        CalendarDaily,
    },
    mixins: [
    ],
    props: {
        viewedFullObject: {
            type: Object,
            required: true,
        },
        events: {
            type: [Array, null],
            default: null,
        },
    },
    emits: [
        'closeModal',
        'selectEvent',
        'newEvent',
    ],
    data() {
        return {

        };
    },
    computed: {

    },
    methods: {
        closeModal() {
            this.$emit('closeModal');
        },
        selectEvent(event) {
            this.$emit('selectEvent', event);
        },
        newEvent(event) {
            this.$emit('newEvent', event);
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-daily-modal {

} */

</style>
