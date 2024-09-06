import FeaturePageSettings from '@/components/pageSettings/FeaturePageSettings.vue';
import RoundedIcon from '@/components/buttons/RoundedIcon.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

export default {
    components: {
        FeaturePageSettings,
        RoundedIcon,

    },
    mixins: [
        interactsWithModal,
    ],
    data() {
        return {
            isPageSettingsOpen: false,
            defaultSettingsTab: '',
            defaultSettingsView: 'PAGE',
        };
    },
    methods: {
        openPageSettings(tab = '', selectedView = 'PAGE') {
            this.isPageSettingsOpen = true;
            this.defaultSettingsView = selectedView;
            if (tab && _.isString(tab)) {
                this.defaultSettingsTab = tab;
            }
        },
        closePageSettings() {
            this.defaultSettingsTab = '';
            this.defaultSettingsView = 'PAGE';
            this.isPageSettingsOpen = false;
        },
        goToShortcut({ tab, selectedView }) {
            this.openPageSettings(tab, selectedView);
        },
    },
};
