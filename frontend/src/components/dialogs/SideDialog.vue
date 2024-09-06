<template>
    <Teleport
        :to="$root.$el"
    >
        <div
            v-blur.teleport="getParent()"
            class="c-side-dialog"
            :class="dialogClasses"
        >
            <div
                v-if="hasOverlay"
                class="h-full w-full fixed top-0 left-0 bg-cm-950 opacity-50"
                @click="closeSide"
            >

            </div>
            <div
                v-if="detailsOpen"
                class="c-side-dialog__inner z-over relative"
                :class="[innerPadding, widthClasses]"
            >
                <IconHover
                    v-if="!hideClose"
                    class="c-side-dialog__close"
                    icon="fal fa-times"
                    @click="closeSide"
                >
                </IconHover>

                <slot>
                </slot>
            </div>
        </div>
    </Teleport>
</template>

<script>

import IconHover from '@/components/buttons/IconHover.vue';

export default {
    name: 'SideDialog',
    components: {
        IconHover,
    },
    mixins: [
    ],
    props: {
        sideOpen: Boolean,
        onWhichSide: {
            type: String,
            default: 'RIGHT',
            validator(val) {
                return ['LEFT', 'RIGHT'].includes(val);
            },
        },
        hideClose: Boolean,
        innerPadding: {
            type: String,
            default: 'p-6',
        },
        hasOverlay: Boolean,
        maxWidthClass: {
            type: String,
            default: 'sm:w-450p',
        },
    },
    emits: [
        'closeSide',
    ],
    data() {
        return {
            detailsOpen: false,
        };
    },
    computed: {
        sideClass() {
            if (this.onWhichSide === 'RIGHT') {
                return 'right-0';
            }
            return 'left-0';
        },
        dialogClasses() {
            return [
                this.sideClass,
                { 'w-0': !this.detailsOpen },
            ];
        },
        widthClasses() {
            return `w-300p ${this.maxWidthClass}`;
        },
    },
    methods: {
        // The dialog should behave as an overlay when it's open, and a regular
        // teleported component when it's closed.
        // We could just use v-if but that would affect the smoothness of the transition.
        getParent() {
            return this.detailsOpen ? null : this.$parent;
        },
        closeSide() {
            this.detailsOpen = false;
            this.$emit('closeSide');
        },
        openSide() {
            this.detailsOpen = true;
        },
    },
    watch: {
        sideOpen: {
            immediate: true,
            handler(val) {
                if (val) {
                    this.openSide();
                } else {
                    this.closeSide();
                }
            },
        },
    },
};
</script>

<style scoped>

.c-side-dialog {
    box-shadow: -4px 0 12px 0 #ccc;
    /*transition: width 0.2s ease-in-out;
    width: 0;*/

    /*&--open {
        transition: width 0.2s ease-in-out;
        width: calc(100% - 20px);
    }*/

    @apply
        bg-cm-00
        fixed
        h-full
        max-h-full
        overflow-x-hidden
        overflow-y-auto
        top-0
        z-modal
    ;

    &__inner {
        @apply
            bg-cm-00
            h-full
            overflow-x-hidden
            relative
        ;

    }

    &__close {
        right: 16px;
        top: 6px;

        @apply
            absolute
            z-over
        ;
    }
}

</style>
