import PageWizardDialog from '@/components/customize/PageWizardDialog.vue';

import interactsWithFullDialog from '@/vue-mixins/interactsWithFullDialog.js';

export default {
    components: {
        PageWizardDialog,
    },
    mixins: [
        interactsWithFullDialog,
    ],
};
