import FeatureSource from '@/components/features/FeatureSource.vue';
import DownloadButton from '@/components/buttons/DownloadButton.vue';

export default {
    components: {
        FeatureSource,
        DownloadButton,
    },
    props: {
        pin: {
            type: Object,
            required: true,
        },
        pinboard: {
            type: [Object, null],
            default: null,
        },
    },
    computed: {
        description() {
            return this.pin.description;
        },
        name() {
            return this.pin.name;
        },
        image() {
            return this.pin.image.url;
        },
        downloadUrl() {
            return this.pin.image.downloadUrl;
        },

        featureItem() {
            return this.pin;
        },
    },
};
