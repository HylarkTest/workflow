<template>
    <div
        v-if="!isResponsiveDisplay"
        class="c-layout-side feature-page__side"
        :class="layoutSideClass"
        :style="widthStyle"
    >
        <LayoutSideInner
            class="pt-2 pb-4"
            :isResponsiveDisplay="isResponsiveDisplay"
            @minimizeSide="minimizeSide"
        >
            <slot>
            </slot>
        </LayoutSideInner>
    </div>

    <SideDialog
        v-else
        :sideOpen="true"
        innerPadding=""
        onWhichSide="LEFT"
        :hideClose="true"
        :hasOverlay="true"
        @closeSide="minimizeSide"
    >
        <LayoutSideInner
            class="pt-2 pb-4"
            :isResponsiveDisplay="isResponsiveDisplay"
            @minimizeSide="minimizeSide"
        >
            <slot>
            </slot>
        </LayoutSideInner>
    </SideDialog>
</template>

<script>

import LayoutSideInner from '@/components/layout/LayoutSideInner.vue';
import SideDialog from '@/components/dialogs/SideDialog.vue';

export default {
    name: 'LayoutSide',
    components: {
        LayoutSideInner,
        SideDialog,
    },
    mixins: [
    ],
    props: {
        width: {
            type: Number,
            default: 260,
        },
        isResponsiveDisplay: Boolean,
        layoutSideClass: {
            type: String,
            default: '',
        },
    },
    emits: [
        'minimizeSide',
    ],
    data() {
        return {
            isSideOpen: false,
        };
    },
    computed: {
        pixelWidth() {
            return `${this.width}px`;
        },
        widthStyle() {
            return { width: this.pixelWidth, minWidth: this.pixelWidth };
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

.c-layout-side {
    @apply
        z-cover
    ;
}

</style>
