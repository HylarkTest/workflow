<template>
    <LayoutPageSimple
        class="o-home-page min-h-full relative"
    >
        <div
            class="o-home-page__background"
        >
            <QuarterCircle
                class="o-home-page__quarter"
                point="top-left"
            >
            </QuarterCircle>
        </div>
        <div class="o-home-page__main min-h-full">
            <HomeDisplay
                v-if="authenticatedUser"
                class="w-full px-4 md:px-8 lg:pr-0 py-8 min-w-0"
                :user="authenticatedUser"
                :personalBasePreferences="personalBasePreferences"
                :everyoneBasePreferences="everyoneBasePreferences"
            >
            </HomeDisplay>

            <div class="o-home-page__right px-4 md:px-8 py-8">
                <HomeSide
                    :personalBasePreferences="personalBasePreferences"
                >
                </HomeSide>
            </div>
        </div>
    </LayoutPageSimple>
</template>

<script>

import HomeDisplay from '@/components/home/HomeDisplay.vue';
import HomeSide from '@/components/home/HomeSide.vue';
import LayoutPageSimple from '@/components/layout/LayoutPageSimple.vue';

import { activeBase } from '@/core/repositories/baseRepository.js';
import interactsWithAuthenticatedUser from '@/vue-mixins/interactsWithAuthenticatedUser.js';
// import interactsWithSupportWidget from '@/vue-mixins/support/interactsWithSupportWidget.js';

export default {
    name: 'HomePage',
    components: {
        HomeDisplay,
        HomeSide,
        LayoutPageSimple,
    },
    mixins: [
        interactsWithAuthenticatedUser,
        // interactsWithSupportWidget,
    ],
    props: {

    },
    apollo: {
    },
    data() {
        return {
            // supportPropsObj: {
            //     sectionName: 'Home page',
            //     sectionTitle: 'The home page',
            //     tips: [],
            // },
        };
    },
    computed: {
        todayLength() {
            return this.todayEvents.length;
        },
        personalBasePreferences() {
            return this.authenticatedUser.baseSpecificPreferences();
        },
        everyoneBasePreferences() {
            return activeBase().preferences;
        },
    },
    methods: {
    },
    created() {
    },
};
</script>

<style scoped>

.darkmode {
    .o-home-page {
        &__background {
            @apply
                opacity-100
            ;
        }

        &__quarter {
            @apply
                opacity-100
            ;
        }
    }
}

.o-home-page {
    &__background {
        @apply
            absolute
            bg-gradient-to-br
            bottom-0
            from-primary-200
            h-full
            left-0
            opacity-50
            to-secondary-200
            top-0
            w-full
            z-0
        ;
    }

    &__quarter {
        height: 300px;
        width: 300px;

        @apply
            bg-secondary-200
            fixed
            opacity-75
        ;
    }

    &__main {
        @apply
            flex
            flex-col
            items-start
            relative
            z-over
        ;

        @media (min-width: 1024px) {
            & {
                @apply
                    flex-row
                ;
            }
        }
    }

    &__right {
        @apply
            shrink-0
            w-full
        ;

        @media (min-width: 1024px) {
            & {
                width: 340px;
            }
        }
    }

    /*&__box {
        @apply
            bg-cm-00
            m-4
            p-4
            rounded-lg
            shadow-lg
        ;
    }

    &__status {
        @apply
            bg-gradient-to-tr
            text-cm-00
            font-bold
            mx-2
            p-2
            rounded-md
            w-1/3
        ;
    }

    &__title {
        @apply
            text-cm-700
            font-bold
            text-2xl
        ;
    }*/
}

</style>
