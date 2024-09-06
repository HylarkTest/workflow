<template>
    <component
        :is="element"
        v-bind="$attrs"
    >
        <slot />
    </component>
</template>
<script>
import _ from 'lodash';

import scrolling from './scrolling.js';

export default {
    name: 'LoadMore',
    mixins: [
        scrolling,
    ],
    props: {
        element: {
            type: String,
            default: 'div',
        },
        cursor: {
            type: [String, null],
            default: null,
        },
        hasNext: Boolean,
    },
    emits: [
        'nextPage',
    ],
    methods: {
        loadMore: _.throttle(function loadMore() {
            if (this.hasNext) {
                this.scroll = this.scrollTop();
                const bottom = this.$el.getBoundingClientRect().bottom;
                if (this.pageBottom() >= (bottom - 100)) {
                    this.$emit('nextPage');
                }
            }
        }, 300),
        onScroll() {
            this.loadMore();
        },
    },
    watch: {
        scrollEl() {
            if (!this.scrollElHasOverflow()) {
                this.loadMore();
            }
        },
        cursor() {
            this.$nextTick(() => {
                if (!this.scrollElHasOverflow()) {
                    this.loadMore();
                }
            });
        },
    },
};
</script>
