import {
    createApp, provide, h,
} from 'vue';
import { ApolloClients } from '@vue/apollo-composable';
import App from './App.vue';
import interactsWithAuthenticatedUser from '@/vue-mixins/interactsWithAuthenticatedUser.js';

import {
    buildColorObj,
    createAccentClasses,
    defaultAccentColor,
    extraColors,
} from '@/core/display/accentColors.js';

import { userPreferences } from '@/core/repositories/preferencesRepository.js';
import { feedbackInfo, closeFeedback } from '@/core/feedback.js';
import { supportInfo } from '@/core/support.js';
import { activeBase, nextBase } from '@/core/repositories/baseRepository.js';
import { openModals } from '@/components/dialogs/Modal.vue';
import { globalModalsArr } from '@/core/modals.js';
import createProvider from '@/vue-apollo.js';

const apolloProvider = createProvider();

const app = createApp({
    render() {
        return h(App, {
            feedbackInfo: this.feedbackInfo,
            supportInfo: this.supportInfo,
        });
    },
    setup() {
        provide(ApolloClients, apolloProvider.clients);
    },
    mixins: [
        interactsWithAuthenticatedUser,
    ],
    data() {
        return {
            feedbackInfo,
            supportInfo,
            preferences: null,
            showSignoutLoader: false,
        };
    },
    computed: {
        globalModals() {
            return globalModalsArr.value || [];
        },
        isSwitchingBase() {
            if (nextBase.value) {
                return true;
            }
            // The `nextBase` value is set and unset in middleware which is handled
            // during the `beforeEach` lifecycle of the router. This means there
            // is a very brief window where the `nextBase` value is unset and the
            // the route hasn't switched yet, so Apollo tries to reload all the
            // active queries on the new client causing errors.
            // So here we wait until the route has finished switching before marking
            // the base as switched.
            if (this.$route.params.baseId) {
                return this.$route.params.baseId !== activeBase()?.id;
            }
            return false;
        },
        isInDarkMode() {
            return this.colorMode === 'DARK';
        },
        colorMode() {
            return this.preferences?.colorMode || 'LIGHT';
        },
        accentColor() {
            return activeBase()?.preferences?.accentColor || defaultAccentColor;
        },

        // Route info
        routeMeta() {
            return this.$route.meta;
        },
        accessPages() {
            return this.routeMeta?.access || false;
        },
        supportSite() {
            return this.routeMeta?.support || false;
        },
        errorSite() {
            return this.routeMeta?.error || false;
        },
        noDarkMode() {
            return this.supportSite
                || this.accessPages
                || this.errorSite;
        },
        useDefaultColorScheme() {
            return this.accessPages
                || this.supportSite
                || this.errorSite;
        },
        enforcedColorMode() {
            if (this.noDarkMode) {
                return 'LIGHT';
            }
            return this.colorMode;
        },
        enforcedColorScheme() {
            if (this.useDefaultColorScheme) {
                return defaultAccentColor;
            }
            return this.accentColor;
        },

        // Modals
        listOfOpenModalKeys() {
            const modals = openModals.value;
            return modals?.map((val) => {
                return val.modalKey;
            });
        },
    },
    methods: {
        closeFeedback(id) {
            closeFeedback(id);
        },
        extraColorDisplay(val, intensity = '600') {
            let obj = _.find(extraColors, { val });
            if (!obj) {
                obj = buildColorObj(val);
            }
            return obj[_.lowerCase(this.colorMode)][intensity];
        },
        setAccentColors() {
            const css = createAccentClasses(this.enforcedColorScheme, this.enforcedColorMode);
            this.$nextTick(() => {
                const styleNode = document.getElementById('accent-colors');
                styleNode.innerHTML = css;
            });
        },
        setColorMode() {
            const bodyClassList = document.body.classList;
            const colorMode = this.enforcedColorMode;
            if (colorMode === 'DARK' && !bodyClassList.contains('darkmode')) {
                bodyClassList.add('darkmode');
            } else if (colorMode === 'LIGHT') {
                bodyClassList.remove('darkmode');
            }
        },
    },
    watch: {
        // accentColor: {
        //     immediate: true,
        //     handler() {
        //         this.setAccentColors();
        //     },
        // },
        // colorMode: {
        //     immediate: true,
        //     handler() {
        //         this.setColorMode();
        //         this.setAccentColors();
        //     },
        // },
        enforcedColorScheme: {
            immediate: true,
            handler() {
                this.setAccentColors();
            },
        },
        enforcedColorMode: {
            immediate: true,
            handler() {
                this.setColorMode();
                this.setAccentColors();
            },
        },
    },
    async created() {
        this.preferences = userPreferences;
    },
});

app.use(apolloProvider);

window.app = app;

export default app;
