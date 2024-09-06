import EventRepeatConfirm from '@/components/events/EventRepeatConfirm.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

import {
    deleteEvent,
    updateEvent,
} from '@/core/repositories/eventRepository.js';

export default {
    components: {
        EventRepeatConfirm,
    },
    mixins: [
        interactsWithModal,
    ],
    data() {
        return {
            recurrenceModalAction: null,
            processing: false,
            processingDelete: false,
        };
    },
    computed: {
        hasEventRecurrence() {
            return false; // In component
        },
    },
    methods: {
        recurrenceCheck(event) {
            return event?.recurrence
                || event?.isRecurringInstance();
        },
        openRecurrenceModal(actionKey) {
            this.recurrenceModalAction = actionKey;
            this.openModal();
        },
        completeAction(affectedEventsKey) {
            if (this.recurrenceModalAction === 'CHANGE') {
                this.saveEvent(affectedEventsKey);
            } else {
                this.deleteEvent(affectedEventsKey);
            }
            this.recurrenceModalAction = null;
        },
        deleteEvent() {
            // In component, with the component-specific arguments
        },
        saveEvent() {
            // In component, with the component-specific arguments
        },
        deleteEventOptions() {
            if (this.hasEventRecurrence) {
                this.openRecurrenceModal('DELETE');
            } else {
                this.deleteEvent();
            }
        },
        saveEventOptions() {
            if (this.hasEventRecurrence) {
                this.openRecurrenceModal('CHANGE');
            } else {
                this.saveEvent();
            }
        },
        async saveEventIncludingRepeat(form, item, affectedEventsKey) {
            this.processing = true;
            try {
                await updateEvent(form, item, affectedEventsKey === 'ALL_FUTURE');
                this.$emit('closeModal');
            } finally {
                this.processing = false;
            }
        },
        async deleteEventIncludingRepeat(item, affectedEventsKey) {
            this.processingDelete = true;
            try {
                await deleteEvent(item, affectedEventsKey === 'ALL_FUTURE');
                this.$emit('closeModal');
            } finally {
                this.processingDelete = false;
            }
        },
    },
};
