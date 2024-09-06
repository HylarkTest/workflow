import eventBus from '@/core/eventBus.js';

export default {
    data() {
        return {
            listeners: {
                // defined in component
                // [handler]: [event1, event2, ...]
            },
        };
    },
    created() {
        Object.keys(this.listeners).forEach((handler) => {
            this.listeners[handler].forEach((event) => {
                eventBus.listen(event, this[handler]);
            });
        });
    },
    unmounted() {
        Object.keys(this.listeners).forEach((handler) => {
            this.listeners[handler].forEach((event) => {
                eventBus.drop(event, this[handler]);
            });
        });
    },
};
