<template>
    <div
        class="c-upgrade-message"
        :class="{ 'c-upgrade-message--overlay': isOverlay }"
    >
        <div
            v-if="isOverlay"
            class="c-upgrade-message__overlay"
        >
        </div>

        <div class="center flex-col h-full relative">
            <div
                class="center rounded-full h-16 w-16 mb-4"
                :class="elementsBgClass"
            >
                <i
                    class="fa-duotone fa-rocket-launch text-3xl"
                    :style="duotoneColors(accentColor)"
                >
                </i>
            </div>

            <h1
                v-t="info.title"
                class="font-bold w-300p text-center mb-2 text-base"
            >
            </h1>

            <p
                v-t="info.subtitle"
                class="c-upgrade-message__subtitle"
            >
            </p>

            <div class="flex mt-2">
                <a
                    v-t="'common.learnMore'"
                    :href="pricing"
                    class="button hover:bg-primary-200 mr-2 block"
                    type="button"
                    :class="elementsBgClass"
                >
                    Learn more
                </a>

                <RouterLink
                    v-t="'common.upgrade'"
                    :to="{ name: 'settings.plans' }"
                    class="button text-cm-00 bg-primary-600 hover:bg-primary-500 block"
                    type="button"
                >
                    Upgrade
                </RouterLink>
            </div>
        </div>
    </div>
</template>

<script>

import providesColors from '@/vue-mixins/style/providesColors.js';

import config from '@/core/config.js';

export default {
    name: 'UpgradeMessage',
    components: {

    },
    mixins: [
        providesColors,
    ],
    props: {
        isOverlay: Boolean,
        info: {
            type: Object,
            required: true,
            validator(info) {
                return _.has(info, 'title')
                    && _.has(info, 'subtitle');
                // Language paths
                // Other keys are "tips" which contains the base language path
                // e.g. 'tips.spaces.focus'
            },
        },
        elementsBgClass: {
            type: String,
            default: 'bg-cm-100',
        },
    },
    data() {
        return {

        };
    },
    computed: {
        landingUrl() {
            return config('app.landing-url');
        },
        pricing() {
            return `${this.landingUrl}/pricing`;
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

.c-upgrade-message {
    @apply
        flex-col
        items-center
        justify-center
    ;

    &--overlay {
        @apply
            absolute
            h-full
            right-0
            top-0
            z-over
        ;
    }

    &__overlay {
        @apply
            absolute
            bg-cm-00
            h-full
            opacity-90
            right-0
            top-0
            w-full
        ;
    }

    &__subtitle {
        max-width: 500px;

        @apply
            leading-snug
            text-center
            text-sm
        ;
    }

    .fa-duotone {
        --fa-secondary-opacity: 1.0;
    }
}

</style>
