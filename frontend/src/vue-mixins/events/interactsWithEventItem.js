import interactsWithEventRecurrence from '@/vue-mixins/events/interactsWithEventRecurrence.js';

export default {
    mixins: [
        interactsWithEventRecurrence,
    ],
    emits: [
        'update:processing',
    ],
    computed: {
        hasEventRecurrence() {
            return !!this.recurrenceCheck(this.event);
        },
    },
    methods: {
        updateProcessing(processingType, state) {
            this.$emit('update:processing', { processingType, state });
        },
        async deleteEvent(affectedEventsKey) {
            this.updateProcessing('delete', true);
            try {
                await this.deleteEventIncludingRepeat(this.event, affectedEventsKey);
            } catch (error) {
                this.updateProcessing('delete', false);
                throw error;
            }
        },
        deleteItem() {
            this.deleteEventOptions();
        },
    },
};
