import interactsWithAssigneesPicker from '@/vue-mixins/features/interactsWithAssigneesPicker.js';

export default {
    mixins: [
        interactsWithAssigneesPicker,
    ],
    computed: {
        featureItem() {
            return {}; // Add in component
        },
        assigneeGroupsObject() {
            return this.featureItem;
        },
    },
};
