import * as scrolling from '@/core/helpers/scrolling.js';

/*
 Instead of storing the scroll position every time it changes this mixin will
 search for any `onScroll` or `onResize` methods to call when the event is
 triggered. This is potentially more efficient because typically components
 want behaviour to change at certain points instead of every single scroll move.
 By storing the scroll position we force Vue to recalculate all dependent fields
 every time the scroll changes so though it looks efficient because the onScroll
 method is just changing one value it is deceptive because there is more going
 behind the scenes. This way the developer is aware that the function they write
 will be called for every scroll and so will be encouraged to write it
 efficiently.
 */

const listeners = ['onScroll', 'onResize', 'onScrollAndResize'];

export default {
    created() {
        this._removeListeners = [];
    },
    mounted() {
        // Wait until other mounted hooks have run
        this.$nextTick(() => {
            const el = _.isFunction(this.getScrollEl) ? this.getScrollEl() : null;
            listeners.forEach((method) => {
                if (_.isFunction(this[method])) {
                    this._removeListeners.push(
                        scrolling[method](this[method], el, true)
                    );
                }
            });
        });
    },
    unmounted() {
        this._removeListeners.forEach((unwatch) => {
            unwatch();
        });
    },
};
