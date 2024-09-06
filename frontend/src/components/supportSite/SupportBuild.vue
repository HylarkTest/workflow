<template>
    <div class="o-support-build flex flex-col h-full">

        <SupportNav
            class="o-support-build__nav"
        >
        </SupportNav>

        <div
            class="o-support-build__main flex flex-1 relative"
        >
            <div
                class="md:hidden fixed left-2"
                :class="isSideOpen ? 'top-2 z-dialog' : 'top-16'"
            >
                <IconHover
                    :icon="isSideOpen ? 'far fa-times' : 'far fa-bars'"
                    iconSize="lg"
                    @click="toggleSide"
                >
                </IconHover>
            </div>

            <div
                class="o-support-build__menu"
            >
                <div
                    v-if="isSideOpen"
                    class="fixed top-0 left-0 bg-cm-900 opacity-50 h-full w-full md:hidden"
                    @click.stop="closeSide"
                >
                </div>
                <SupportSide
                    class="o-support-build__side md:block"
                    :class="{ 'o-support-build__side--open': isSideOpen }"
                >
                </SupportSide>
            </div>

            <div class="px-10 py-10 md:px-8 flex-1 w-full">
                <RouterView>
                </RouterView>
            </div>
        </div>
    </div>
</template>

<script>

import SupportNav from './SupportNav.vue';
import SupportSide from './SupportSide.vue';
import IconHover from '@/components/buttons/IconHover.vue';

import resetsStyleClassToBrandLocally from '@/vue-mixins/style/resetsStyleClassToBrandLocally.js';

export default {
    name: 'SupportBuild',
    components: {
        SupportNav,
        SupportSide,
        IconHover,
    },
    mixins: [
        resetsStyleClassToBrandLocally,
    ],
    props: {

    },
    data() {
        return {
            isSideOpen: false,
        };
    },
    computed: {

    },
    methods: {
        toggleSide() {
            this.isSideOpen = !this.isSideOpen;
        },
        closeSide() {
            this.isSideOpen = false;
        },
    },
    created() {
    },
};
</script>

<style scoped>

.o-support-build {

    &__menu {
        @apply
            fixed
            left-0
            top-0
            z-nav
        ;

        @media (min-width: 768px) {
            & {
                @apply
                    relative
                    z-0
                ;
            }
        }
    }

    &__side {
        /* stylelint-disable plugin/no-unsupported-browser-features */
        height: 100vh;
        max-height: 100vh;
        max-height: -webkit-fill-available;
        /* stylelint-enable */

        @apply
            hidden
            overflow-y-auto
            shadow-lg
        ;

        &--open {
            @apply
                block
            ;
        }

        @media (min-width: 768px) {
            & {
                /* stylelint-disable plugin/no-unsupported-browser-features */
                height: calc(100vh - 60px);
                max-height: calc(100vh - 60px);
                max-height: -webkit-fill-available;
                /* stylelint-enable */
                top: 60px;

                @apply
                    block
                    shadow-none
                    shrink-0
                    sticky
                ;
            }
        }
    }
}

</style>
