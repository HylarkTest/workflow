<!-- Scrollable container where the intended behaviour is that the container
stays the max height, such as the size of the screen, or the size of the area beneath a header,
not larger than the screen. This scroll is independent of the full <body> scroll.  -->

<template>
    <div
        class="c-scrollable-box"
        :style="dynamicStyle"
    >
        <slot></slot>
    </div>
</template>

<script>
import _ from 'lodash';
import { onScrollAndResize, smoothScroll } from '@/core/helpers/scrolling.js';

export default {
    name: 'ScrollableBox',
    components: {

    },
    mixins: [
    ],
    props: {
        padding: {
            type: Number,
            default: 56,
        },
    },
    data() {
        return {
            dynamicStyle: {},
        };
    },
    computed: {

    },
    methods: {
        getMutationFunction(mutation) {
            // add "data-scrollable-box-observable" to the element that should trigger a resize.
            // look at FeatureSide.vue for an example.
            if (mutation.type === 'childList' && _.has(mutation.target.dataset, 'scrollableBoxObservable')) {
                return () => smoothScroll(mutation.target.offsetTop, mutation.target);
            }
            return false;
        },
        async updateStyle() {
            this.dynamicStyle.height = 'auto';
            await this.$nextTick();
            const box = this.$el.getBoundingClientRect();
            const bottom = document.body.offsetHeight - this.padding;
            if (box.bottom > bottom) {
                const height = box.height + (bottom - box.bottom);
                this.dynamicStyle.height = `${height}px`;
            } else if (box.bottom < bottom && this.$el.scrollHeight > this.$el.clientHeight) {
                const height = Math.min(box.height + (bottom - box.bottom), this.$el.scrollHeight);
                this.dynamicStyle.height = `${height}px`;
            }
        },
        throttleUpdateStyle: _.throttle(function throttleUpdateStyle() {
            this.updateStyle();
        }, 30), // 30 fps (1000/30) should be smooth enough
    },
    created() {

    },
    mounted() {
        this.unwatch = onScrollAndResize(this.throttleUpdateStyle, this.$el, true);
        this.observer = new MutationObserver((mutationList) => {
            const mutations = Array.from(mutationList);
            const observables = mutations
                .map((mutation) => this.getMutationFunction(mutation))
                .filter((mutationFunction) => _.isFunction(mutationFunction));

            if (observables.length) {
                this.updateStyle();
                observables.forEach((mutationFunction) => mutationFunction());
            }
        });
        this.observer.observe(this.$el, { attributes: false, childList: true, subtree: true });
        this.updateStyle();
    },
    unmounted() {
        this.unwatch();
        this.observer.disconnect();
    },
};
</script>

<style scoped>

.c-scrollable-box {
    @apply
        max-h-full
        overflow-y-auto
    ;
}

</style>
