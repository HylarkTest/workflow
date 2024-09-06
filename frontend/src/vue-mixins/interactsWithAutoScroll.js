import { smoothScroll } from '@/core/helpers/scrolling.js';

export default {
    data() {
        return {
            scrollTarget: null,
        };
    },
    methods: {
        scrollToElement(el) {
            smoothScroll(el.offsetTop, this.$el);
        },
        // View DailyColumn.vue for an example.
        // use setRef as the :ref key in a v-for loop. Pass a unique key.
        setRef(id) {
            return (el) => {
                if (this.scrollTarget === id) {
                    this.scrollToElement(el);
                    this.scrollTarget = null;
                }
            };
        },
        setScrollTarget(id) {
            this.scrollTarget = id;
        },
    },
};

// CYCLE:

// 1) A new event is created and the eventBus calls this.setScrollTarget(id).
// At this point, the DOM has not rerendered so the element matching the new event does NOT yet exist.

// 2) set a new scrollTarget, so that when the DOM rerenders, the element matching the new event will be scrolled to.

// 3) Once the scrollTarget has been found, clear the scrollTarget.

// The DOM has now rerendered containing the new event,
// the new event has been scrolled to and the scrollTarget has been cleared.
