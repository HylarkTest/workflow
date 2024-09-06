<template>
    <div class="o-cookie-banner hylark-app">
        <div class="o-cookie-banner__description">
            Hylark uses cookies and local storage to ensure you get the best experience on our website.
            For more information, see our
            <a
                :href="cookiePolicyLink"
                rel="noreferrer noopener"
                target="_blank"
                class="underline inline-block transition-2eio"
                @click.stop
            >
                Cookies Policy
            </a>
            .
        </div>

        <div class="o-cookie-banner__actions">
            <div class="o-cookie-banner__primary">
                <button
                    class="button--sm button-primary"
                    type="button"
                    @click="acceptAll"
                >
                    Accept all
                </button>
                <button
                    class="button--sm button-primary--border bg-transparent"
                    type="button"
                    @click="rejectAll"
                >
                    Necessary only
                </button>
            </div>
            <button
                class="button--sm underline bg-transparent"
                type="button"
                @click="customizeCookies"
            >
                Customize
            </button>
        </div>

        <CookiePanel
            v-if="showCookiePanel"
            @closeCookiesPanel="closeCookiesPanel"
            @closeCookiesBanner="closeCookiesBanner"
        >
        </CookiePanel>
    </div>
</template>

<script>

import CookiePanel from './CookiePanel.vue';
import { COOKIE_SET } from '@/core/helpers/cookieHelpers.js';

import interactsWithCookieSettings from '@/vue-mixins/interactsWithCookieSettings.js';
import interactsWithEventBus from '@/vue-mixins/interactsWithEventBus.js';

export default {
    name: 'CookieBanner',
    components: {
        CookiePanel,
    },
    mixins: [
        interactsWithCookieSettings,
        interactsWithEventBus,
    ],
    props: {
    },
    emits: [
        'closeCookiesBanner',
    ],
    data() {
        return {
            showCookiePanel: false,
            listeners: {
                closeCookiesBanner: [COOKIE_SET],
            },
        };
    },
    computed: {
    },
    methods: {
        customizeCookies() {
            this.showCookiePanel = true;
        },
        closeCookiesPanel() {
            this.showCookiePanel = false;
        },
        closeCookiesBanner() {
            this.$emit('closeCookiesBanner');
        },
    },
    created() {
    },
};
</script>

<style scoped>
.o-cookie-banner {
    box-shadow: 0 0 15px 0 #999;

    @apply
        bg-opacity-90
        bg-white
        bottom-0
        fixed
        flex
        flex-col
        items-center
        justify-between
        p-4
        w-full
        z-40
    ;

    &__description {
        text-wrap: pretty;

        @apply
            mb-5
            text-sm
        ;
    }

    &__actions {
        @apply
            flex
            gap-2
        ;
    }

    &__primary {
        @apply
            flex
            gap-2
        ;
    }

    @media (min-width: 768px) {
        @apply
            flex-row
            px-8
        ;

        &__description {
            @apply
                flex-1
                mb-0
                mr-3
            ;
        }
    }

    @media (min-width: 500px) {
        &__actions {
            @apply
                gap-3
            ;
        }

        &__primary {
            @apply
                gap-3
            ;
        }
    }
}
</style>
