import FeatureSource from '@/components/features/FeatureSource.vue';

export default {
    components: {
        FeatureSource,
    },
    props: {
        link: {
            type: Object,
            required: true,
        },
        linkList: {
            type: [Object, null],
            default: null,
        },
    },
    computed: {
        url() {
            return this.link.url;
        },
        featureItem() {
            return this.link;
        },
    },

};
