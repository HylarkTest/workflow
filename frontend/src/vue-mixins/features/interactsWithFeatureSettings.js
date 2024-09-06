import FeaturesSettingsModal from '@/components/features/FeaturesSettingsModal.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

export default {
    components: {
        FeaturesSettingsModal,

    },
    mixins: [
        interactsWithModal,
    ],
    data() {
        return {
            isPageSettingsOpen: false,
            defaultSettingsTab: '',
        };
    },
    methods: {
        openPageSettings(tab = '') {
            this.isPageSettingsOpen = true;
            if (tab) {
                this.defaultSettingsTab = tab;
            }
        },
        closePageSettings() {
            this.defaultSettingsTab = '';
            this.isPageSettingsOpen = false;
        },
    },
};
