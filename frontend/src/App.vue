<template>
    <div
        class="app hylark-app"
        :class="appClass"
    >
        <template
            v-if="globalModalsLength"
        >
            <Modal
                v-for="globalModal in globalModals"
                :key="globalModal.tempId"
                v-bind="globalModal.attributes"
                @closeModal="globalModal.listeners.closeModal"
            >
                <template
                    #header
                >
                    <component :is="globalModal.slots.header" />
                </template>

                <component
                    v-bind="globalModal.props"
                    :is="globalModal.component"
                    v-on="globalModal.listeners"
                >
                </component>
            </Modal>
        </template>

        <CookieBanner
            v-if="showCookiesBanner && !showLoader"
            @closeCookiesBanner="closeCookiesBanner"
        >
        </CookieBanner>

        <transition
            name="t-fade-out"
        >
            <LoaderMain
                v-if="showLoader"
            >
            </LoaderMain>
        </transition>

        <LoaderSignout
            v-if="showSignoutLoader"
        >
        </LoaderSignout>

        <LoaderSwitchBase
            v-if="showSwitchingBasesLoader"
        >
        </LoaderSwitchBase>

        <div
            class="app__body"
            :class="bodyClass"
        >
            <NavMain
                v-if="showMainNav"
            >
            </NavMain>

            <div
                v-if="!isSwitchingBase"
                class="w-full min-w-0 md:py-0 min-h-full bg-cm-00 flex-1"
            >
                <RouterView
                    :key="routerViewKey"
                    :showMainLoader="showLoader"
                /> <!-- DO NOT EDIT -->
            </div>

            <div
                class="bottom-4 fixed right-4 z-feedback flex flex-col items-end"
            >
                <FeedbackPopups
                    class="mb-2"
                    :feedbackInfo="feedbackInfo"
                >
                </FeedbackPopups>
            </div>

            <div
                v-if="!showLoader && showFooter"
                class="app__footer pointer-events-none"
                :class="footerClasses"
            >
                <div
                    class="relative"
                    :class="{ 'py-1 px-2': supportInfoLength }"
                >
                    <div
                        v-if="supportInfoLength"
                        class="app__overlay"
                    >
                    </div>

                    <div
                        v-if="supportInfoLength"
                        class="relative z-over pointer-events-auto"
                    >
                        <SupportPrompts
                            :supportArr="supportInfo"
                        >
                        </SupportPrompts>
                    </div>
                </div>

                <div
                    class="relative p-1"
                >
                    <div
                        class="app__overlay"
                    >
                    </div>

                    <LayoutWidget
                        v-if="showWidget"
                        class="pointer-events-auto"
                        :widgets="widgets"
                    >
                    </LayoutWidget>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import { watch } from 'vue';

// import NavMain from '@/components/navigation/NavMain.vue';
import NavMain from '@/components/navigation/NavMain.vue';
import CookieBanner from '@/components/access/CookieBanner.vue';

// import LandingNav from '@/components/landing/LandingNav.vue';
import FeedbackPopups from '@/components/feedback/FeedbackPopups.vue';
import LoaderMain from '@/components/loaders/LoaderMain.vue';
import LoaderSignout from '@/components/loaders/LoaderSignout.vue';
import LoaderSwitchBase from '@/components/loaders/LoaderSwitchBase.vue';

// import ExpiredSession from '@/components/notices/ExpiredSession.vue';
// import NoticeOverlay from '@/components/notices/NoticeOverlay.vue';

import LayoutWidget from '@/components/layout/LayoutWidget.vue';
// import LayoutFooter from '@/components/layout/LayoutFooter.vue';

import { doesCookieExist } from '@/core/helpers/cookieHelpers.js';

import LINKS from '@/graphql/Links.gql';
import { bases } from '@/core/repositories/baseRepository.js';
import GET_UI from '@/graphql/client/GetUI.gql';
import { hasModalsOpen } from '@/components/dialogs/Modal.vue';
import { listenToQuery } from '@/core/repositories/globalSubscriptionsRepository.js';

export default {
    name: 'App',
    components: {
        LoaderMain,
        LoaderSignout,
        LoaderSwitchBase,
        NavMain,
        CookieBanner,
        // LandingNav,
        FeedbackPopups,
        LayoutWidget,
        // LayoutFooter,
    },
    props: {
        feedbackInfo: {
            type: Array,
            required: true,
        },
        supportInfo: {
            type: [Object, null],
            default: null,
        },
    },
    apollo: {
        ui: {
            query: GET_UI,
            update(data) {
                return data.ui;
            },
            client: 'defaultClient',
        },
    },
    data() {
        return {
            initialLoader: false,
            showCookiesBanner: !doesCookieExist('hylark_cookies_permissions'),
            loadingLinks: true,
        };
    },
    computed: {
        globalModals() {
            return this.$root.globalModals;
        },
        globalModalsLength() {
            return this.globalModals.length;
        },
        hasGlobalModals() {
            return this.globalModalsLength > 0;
        },
        showFooter() {
            return !!(this.supportInfoLength || this.showWidget);
        },
        showWidget() {
            return this.appSite && this.widgets?.addShortcuts[0] && this.user;
        },
        supportInfoDisplay() {
            return this.supportInfo?.filter((item) => {
                return !item.value?.hidePromptIf;
            });
        },
        supportInfoLength() {
            return this.supportInfoDisplay?.length;
        },

        // Loaders
        showSignoutLoader() {
            return this.$root.showSignoutLoader;
        },
        showSwitchingBasesLoader() {
            return !this.showLoader && this.isSwitchingBase;
        },
        isSwitchingBase() {
            return this.$root.isSwitchingBase;
        },
        showLoader() {
            // In this order to exit function at strategic times
            if (!this.appSite) {
                return false;
            }
            if (this.initialLoader) {
                return true;
            }
            return this.loadingLinks && !this.shouldSkipLoadingLinks;
        },
        isLoaderActive() {
            // do we want to include this.showLoader here?
            return this.showSignoutLoader || this.showSwitchingBasesLoader;
        },

        user() {
            return this.$root.authenticatedUser;
        },

        pageName() {
            return this.$route.name;
        },

        routeMeta() {
            return this.$route.meta;
        },
        routerViewKey() {
            const route = this.$route;
            // An array of route parameters that indicate a unique page
            let pageParams = [];

            // Most routes have baseId, so we don't want them all to add
            // baseId to `pageParams`, instead we check if the route depends
            // on baseId by looking at the `baseScoped` meta option.
            if (route.meta?.baseScoped && route.params.baseId) {
                pageParams.push('baseId');
            }

            if (route.meta?.pageParams) {
                pageParams = pageParams.concat(route.meta.pageParams);
            }

            if (pageParams.length) {
                return pageParams.map((paramKey) => route.params[paramKey])
                    .filter((param) => param) // Some parameters might be optional so filter any falsey values
                    .join('_');
            }

            return 'app';
        },

        // Any of the pages without the main navigation, e.g. Settings, login, etc...
        noNav() {
            return this.routeMeta?.noNav;
        },
        // landingPages() {
        //     return this.routeMeta?.landing;
        // },
        accessPages() {
            return this.routeMeta?.access;
        },
        supportSite() {
            return this.routeMeta?.support;
        },
        errorSite() {
            return this.routeMeta?.error;
        },

        // Not landing pages, access pages, or support pages
        peripherySite() {
            return this.accessPages
                || this.supportSite
                || this.errorSite;
        },
        appSite() {
            return !this.peripherySite;
        },
        mainAppSite() {
            // The main part of the app with the navigation on the side
            return this.appSite
                && !this.noDistractions;
        },

        showMainNav() {
            return this.pageName && this.mainAppSite && !this.showLoader;
        },
        noDistractions() {
            if (_.isUndefined(this.pageName)) {
                return true;
            }
            return this.noNav;
        },

        appClass() {
            return [
                { 'app--main': this.mainAppSite },
                { 'app--support': this.supportSite },
                { 'app--access': this.accessPages },
                { 'app--no-pointer': this.isLoaderActive },
            ];
        },
        bodyClass() {
            return { 'app__body--main': this.mainAppSite };
        },
        shouldSkipLoadingLinks() {
            return this.$root.isGuest || !this.user.finishedRegistration;
        },
        footerAlignClass() {
            return this.noNav || this.hasModalsOpen
                ? 'nav-spacing--none'
                : `${this.ui?.navExtensionClass} nav-spacing--none`;
        },
        footerClasses() {
            return this.footerAlignClass;
        },
        widgets() {
            return this.user?.baseSpecificPreferences().widgets;
        },
        hasModalsOpen() {
            return this.hasGlobalModals || hasModalsOpen.value;
        },
    },
    methods: {
        stopLoader() {
            setTimeout(() => {
                this.initialLoader = false;
            }, 4000);
        },
        closeCookiesBanner() {
            this.showCookiesBanner = false;
        },
    },
    created() {
        this.stopLoader();

        listenToQuery('pageDeleted', (data) => {
            const hasValidIds = !!this.$route.params?.pageId || !!data.nodeDeleted.node?.id;
            if (hasValidIds && this.$route.params?.pageId === data.nodeDeleted.node?.id) {
                this.$router.push({ name: 'home' });
            }
        });

        watch(bases, () => {
            if (!this.$apollo.queries.links) {
                this.$apollo.addSmartQuery('links', {
                    query: LINKS,
                    update: _.identity,
                    fetchPolicy: 'cache-first',
                    result() {
                        this.loadingLinks = false;
                    },
                    skip() {
                        return this.shouldSkipLoadingLinks;
                    },
                });
            }
        });
    },
};
</script>

<style>

.app {
    font-family: Figtree, sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    @apply
        h-full
        text-cm-900
    ;

    /*&--main {
        @apply
            flex-row
        ;
    }*/

    &--no-pointer {
        pointer-events: none
    }

    &__body {
        @apply
            flex
            min-h-full
            min-w-0
            w-full
        ;
    }

    &__footer {
        @apply
            /*bg-primary-100
            border-primary-200
            border-solid
            border-t*/
            bottom-0
            fixed
            flex
            items-center
            justify-between
            pr-4
            py-1
            w-full
            z-widget
        ;

        /*&--over {
            @apply
                bg-transparent
                border-none
                z-widget
            ;
        }*/
    }

    &__overlay {
        @apply
            absolute
            bg-primary-100
            bottom-0
            h-full
            opacity-60
            right-0
            rounded-lg
            w-full
            z-0
        ;
    }

    .page-full {
        @apply
            h-full
            max-h-screen
        ;
    }

    .page-spacing {
        @apply
            p-8
        ;
    }

    .landing-page-layout {
        margin-top: 44px;

        @apply
            p-12
            w-full
        ;
    }

    .circle-center {
        @apply
            flex
            items-center
            justify-center
            rounded-full
        ;
    }

    .box-light {
        @apply
            border
            border-cm-400
            border-solid
            flex
            items-center
            px-3
            py-2
            relative
        ;
    }

    .center {
        @apply
            flex
            items-center
            justify-center
        ;
    }

    .unclickable {
        @apply
            opacity-25
            pointer-events-none
        ;
    }

    .scale-0 {
        transform: scale(0);
    }

    .scale-05 {
        transform: scale(0.5);
    }

    .scale-1 {
        transform: scale(1);
    }

    .top-dot {
        @apply
            absolute
            bg-azure-600
            h-2
            -right-1
            rounded-full
            -top-1
            w-2
        ;
    }

    .dot {
        @apply
            h-2
            rounded-full
            w-2
        ;
    }

    .no-pointer {
        pointer-events: none;
    }

    .header-1 {
        @apply
            font-bold
            text-2xl
        ;

        @media (min-width: 768px) {
            @apply
                text-4xl
            ;
        }
    }

    .header-2 {
        @apply
            font-semibold
            text-lg
        ;

        @media (min-width: 768px) {
            @apply
                text-xl
            ;
        }
    }

    .header-3 {
        @apply
            font-semibold
            text-cm-700
            text-smbase
        ;

        @media (min-width: 768px) {
            @apply
                text-base
            ;
        }
    }

    .header-uppercase {
        @apply
            font-semibold
            text-cm-600
            text-sm
            uppercase
        ;
    }

    .header-page {
        @apply
            font-bold
            mb-8
            text-3xl
        ;
    }

    .header-uppercase-light {
        @apply
            font-semibold
            text-cm-400
            text-xs
            uppercase
        ;
    }

    .header-list {
        @apply
            font-medium
            text-3xl
            text-primary-900
        ;
    }

    .header-form {
        @apply
            font-semibold
            text-primary-700
            text-xssm
        ;
    }

    .header-display-section {
        @apply
            border-b
            border-primary-300
            border-solid
            font-semibold
            mb-2
            pb-2
            text-cm-600
            text-xl
        ;
    }

    .label-data {
        @apply
            font-semibold
            text-cm-500
            text-sm
        ;
    }

    .label-data--dark {
        @apply
            font-semibold
            text-cm-700
            text-smbase
        ;
    }

    .label-data--primary {
        @apply
            font-semibold
            text-primary-500
            text-xs
        ;
    }

    .label-data--intense {
        @apply
            font-semibold
            text-primary-700
            text-xssm
        ;
    }

    .label-data--light {
        @apply
            font-medium
            text-cm-400
            text-xs
        ;
    }

    .label-data--subtle {
        @apply
            font-medium
            text-cm-600
            text-xssm
        ;
    }

    .list-value-parent {
        @apply
            flex
            flex-wrap
            -m-1
        ;
    }

    .list-value {
        @apply
            m-1
            px-3
            py-1
            rounded-full

        ;
    }

    .h-divider {
        height:  1px;

        @apply
            bg-cm-200
            w-full
        ;
    }

    .v-divider {
        width:  1px;

        @apply
            bg-cm-200
            h-full
        ;
    }

    .grid-container {
        display: grid;
        grid-gap: 1rem;
    }

    .revert-tailwind {
        & img {
            display: revert;
        }
    }

    .screen-container {
        @apply
            rounded-lg
        ;
    }

    .show-html {
        em {
            font-style:  italic;
        }

        ol {
            list-style: revert;
            margin-left: 10px;
        }

        ul {
            list-style: revert;
            margin-left: 10px;
        }

        a {
            color: revert;
            text-decoration: revert;
        }
    }

    .fa-duotone {
        --fa-secondary-opacity: 1.0;
    }

    /* stylelint-disable plugin/no-unsupported-browser-features */
    .grid-fit-full {
        grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
    }

    .grid-fill-card {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    }

    .grid-fill-wide {
        grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    }

    .justify-self-center {
        justify-self: center;
    }
    /* stylelint-enable */
}

.darkmode {
    color-scheme:  dark;
}

</style>
