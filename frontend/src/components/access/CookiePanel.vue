<template>
    <div
        class="o-cookie-panel fixed top-0 left-0 h-full w-full z-50"
    >
        <div class="bg-cm-600 opacity-50 h-full w-full absolute">

        </div>

        <div class="o-cookie-panel__content shadow-primary-800/60 relative">
            <button
                v-if="closable"
                class="rounded-full button-primary--light centered w-6 h-6 absolute top-4 right-4"
                type="button"
                @click="closePanel"
            >
                <i class="far fa-times text-sm">
                </i>
            </button>

            <h2 class="font-bold text-xl mb-6 text-center text-cm-00">
                Let's sort out cookies
            </h2>

            <div class="text-sm text-cm-200 border-b border-solid border-cm-300 pb-4">
                <p class="mb-4">
                    Hylark uses cookies and local storage to ensure you get the best experience on our website.
                </p>
                <ul class="mb-4 list-disc ml-8">
                    <li class="my-1">
                        Necessary cookies make our site work.
                    </li>
                    <li class="my-1">
                        We would like to use your browser's local storage to remember
                        your preferences and enhance your experience on Hylark.
                    </li>
                    <li class="my-1">
                        We would also like to set Matomo analytics cookies to measure use and
                        help improve Hylark.
                    </li>
                </ul>

                <p>
                    Privacy is extremely important to us.
                    For more information on cookies or how your data is protected,
                    please see our
                    <a
                        :href="cookiePolicyLink"
                        rel="noreferrer noopener"
                        target="_blank"
                        class="underline inline-block transition-2eio hover:bg-primary-700"
                        @click.stop
                    >
                        Cookies Policy
                    </a>
                    and
                    <a
                        :href="privacyPolicyLink"
                        rel="noreferrer noopener"
                        target="_blank"
                        class="underline transition-2eio hover:bg-primary-700"
                        @click.stop
                    >
                        Privacy Policy
                    </a>
                    .
                </p>

                <div class="flex flex-col items-center mt-4">
                    <button
                        class="button button-primary--light"
                        type="button"
                        @click="acceptAll"
                    >
                        Accept all cookies
                    </button>
                </div>
            </div>

            <div class="pt-4 text-sm">
                <div class="mb-3">
                    <div>
                        <span class="font-medium">Necessary cookies</span>
                        <p class="text-xssm text-cm-200">
                            These essential cookies enable core functionality such as security,
                            sign in, and accessibility.
                            Hylark is not usable without these cookies.
                        </p>
                    </div>
                    <div>

                    </div>
                </div>

                <div class="flex mb-3">
                    <div>
                        <span class="font-medium">Functional cookies / local storage</span>
                        <p class="text-xssm text-cm-200">
                            Your browser's local storage is used to remember your preferences,
                            and personalize your experience on Hylark.
                            Values stored in local storage exist only on your browser.
                        </p>
                    </div>
                    <div class="ml-2">
                        <ToggleButton
                            v-model="cookieForm.functional"
                        >
                        </ToggleButton>
                    </div>
                </div>

                <div class="flex">
                    <div>
                        <span class="font-medium">Analytics cookies</span>
                        <p class="text-xssm text-cm-200">
                            <a
                                class="underline inline-block transition-2eio hover:bg-primary-700"
                                rel="noreferrer noopener"
                                href="https://matomo.org/"
                                target="_blank"
                            >
                                Matomo
                            </a> cookies help us improve
                            Hylark by gathering information about how it is used.
                            The information collected does not identify anyone,
                            and is not shared outside of Hylark.
                        </p>
                    </div>
                    <div class="ml-2">
                        <ToggleButton
                            v-model="cookieForm.analytics"
                        >
                        </ToggleButton>
                    </div>
                </div>

                <div class="flex flex-col items-center mt-4">
                    <button
                        class="button--sm button-primary--medium mb-2"
                        type="button"
                        @click="submitCookie(cookieForm)"
                    >
                        Save and continue
                    </button>
                    <button
                        class="button--sm button-primary--medium"
                        type="button"
                        @click="rejectAll"
                    >
                        Decline all except essential cookies
                    </button>
                </div>

                <div
                    class="centered mt-6"
                >
                    <div class="rounded-full bg-primary-100 h-20 w-20 mr-2 centered">
                        <img
                            class="h-16"
                            alt="Bird"
                            :src="birdUrl"
                        >
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import ToggleButton from '@/components/inputs/ToggleButton.vue';

import interactsWithCookieSettings from '@/vue-mixins/interactsWithCookieSettings.js';

export default {
    name: 'CookiePanel',
    components: {
        ToggleButton,
    },
    mixins: [
        interactsWithCookieSettings,
    ],
    props: {
        closable: Boolean,
    },
    emits: [
        'closeCookiesPanel',
    ],
    data() {
        return {
            cookieForm: {
                functional: false,
                analytics: false,
            },
        };
    },
    computed: {
    },
    methods: {
    },
    created() {

    },
};
</script>

<style scoped>

.o-cookie-panel {
    /**
     * Now this is a story all about how
     * My life got flipped-turned upside down
     * And I'd like to take a minute, just sit right there
     * I'll tell you how Wordpress messed up everything (including this rhyme)
     *
     * The landing site relies heavily on the browser default styles, which is
     * stupid. So that means we cannot use the reset styles or the tailwind
     * presets.
     * Instead we need to manually add styles to override the defaults in any
     * components that are used on the landing site.
     */
    h2 {
        margin-top: 0;
    }

    p {
        margin-top: 0;
    }

    .centered {
        @apply
            flex
            items-center
            justify-center
        ;
    }

    &__content {
        @apply
            bg-primary-800
            h-full
            max-w-sm
            overflow-auto
            p-6
            relative
            shadow-xl
            text-cm-00
            z-over
        ;
    }
}

</style>
