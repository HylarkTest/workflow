<template>
    <div class="c-layout-side-inner relative bg-cm-00 rounded-2xl">
        <button
            type="button"
            class="c-layout-side-inner__tab transition-2eio"
            :title="$t('common.minimize')"
            @click="minimizeSide"
        >
            <i
                class="fa-regular fa-arrows-to-dot"
            >
            </i>
        </button>

        <!-- <div
            class="absolute bottom-0 top-0 left-0 opacity-60 bg-cm-00 h-full w-full rounded-2xl"
        >
        </div> -->

        <ScrollableBox
            class="px-4"
            :padding="scrollPadding"
        >
            <slot>
            </slot>
        </ScrollableBox>
    </div>
</template>

<script>

import ScrollableBox from '@/components/containers/ScrollableBox.vue';

export default {
    name: 'LayoutSideInner',
    components: {
        ScrollableBox,
    },
    mixins: [
    ],
    props: {
        isResponsiveDisplay: Boolean,
    },
    emits: [
        'minimizeSide',
    ],
    data() {
        return {

        };
    },
    computed: {
        scrollPadding() {
            if (this.isResponsiveDisplay) {
                return 16;
            }
            return 56;
        },
    },
    methods: {
        minimizeSide() {
            this.$emit('minimizeSide');
        },
    },
    created() {

    },
};
</script>

<style scoped>

.c-layout-side-inner {
    &__tab {
        height: 24px;
        width: 24px;

        @apply
            bg-primary-600
            mb-2
            ml-2
            px-1
            relative
            rounded
            text-center
            text-cm-00
            z-over
        ;

        &:hover {
            @apply
                bg-primary-500
            ;
        }
    }
}

</style>
