import RoundedIcon from '@/components/buttons/RoundedIcon.vue';
import HistoryModal from '@/components/history/HistoryModal.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

export default {
    components: {
        RoundedIcon,
        HistoryModal,
    },
    mixins: [
        interactsWithModal,
    ],
    methods: {
        openHistory() {
            this.openModal();
        },
    },
};
