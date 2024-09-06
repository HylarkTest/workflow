import listensToScrollAndResizeEvents from '@/vue-mixins/listensToScrollAndResizeEvents.js';

function getScrollParent(node) {
    let style = getComputedStyle(node);
    const excludeStaticParent = style.position === 'absolute';
    const overflowRegex = /(auto|scroll)/;

    if (style.position === 'fixed') {
        return window;
    }

    // eslint-disable-next-line no-cond-assign
    for (let parent = node; (parent = parent.parentElement);) {
        style = getComputedStyle(parent);
        if (excludeStaticParent && style.position === 'static') {
            // eslint-disable-next-line no-continue
            continue;
        }
        if (overflowRegex.test(style.overflow + style.overflowY) && parent !== document.firstElementChild) {
            return parent;
        }
    }

    return window;
}

export default {
    mixins: [
        listensToScrollAndResizeEvents,
    ],
    props: {
        watchResize: Boolean,
    },
    data() {
        return {
            scrollEl: null,
        };
    },
    computed: {
        scrollElIsWindow() {
            return this.scrollEl === window;
        },
    },
    methods: {
        getScrollEl() {
            return this.scrollEl;
        },
        scrollTo(to) {
            if (this.scrollElIsWindow) {
                window.scrollTo(0, to);
            } else {
                this.scrollEl.scrollTop = to;
            }
        },
        scrollTop() {
            if (this.scrollElIsWindow) {
                return window.scrollY;
            }
            return this.scrollEl.scrollTop;
        },
        pageBottom() {
            if (this.scrollElIsWindow) {
                return document.documentElement.clientTop + window.innerHeight;
            }
            return this.scrollEl.getBoundingClientRect().bottom;
        },
        pageTop() {
            if (this.scrollElIsWindow) {
                return document.documentElement.clientTop;
            }
            return this.scrollEl.getBoundingClientRect().top;
        },
        refreshScrollEl() {
            this.scrollEl = getScrollParent(this.$el);
        },
        onResize() {
            if (this.watchResize) {
                this.refreshScrollEl();
            }
        },
        scrollElHasOverflow() {
            if (this.scrollElIsWindow) {
                return document.documentElement.scrollHeight > window.innerHeight;
            }
            return this.scrollEl.scrollHeight > this.scrollEl.clientHeight;
        },
    },
    async mounted() {
        try {
            this.scrollEl = getScrollParent(this.$el);
        } catch (e) {
            // In some cases the element might not be ready right away, so try
            // again after a tick
            await this.$nextTick();
            this.scrollEl = getScrollParent(this.$el);
        }
    },
};
