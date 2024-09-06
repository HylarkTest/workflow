import FeatureMain from '@/components/features/FeatureMain.vue';

export default {
    components: {
        FeatureMain,
    },
    props: {
        item: {
            type: Object,
            required: true,
        },
        page: {
            type: [Object, null],
            required: true,
        },
        mapping: {
            type: Object,
            default: null,
        },
        topHeaderClass: {
            type: String,
            required: true,
        },
    },
    computed: {
        moddedItem() {
            return {
                ...this.item,
                doNotOpen: true,
                doNotRemove: true,
            };
        },
        defaultAssociations() {
            return [this.moddedItem];
        },
    },
    methods: {
        contextVariables() {
            const variables = {
                forMapping: this.mapping?.id,
                spaceIds: this.mapping.space.id ? [this.mapping.space.id] : null,
                forNode: this.item.id,
            };
            return variables;
        },
    },
};
