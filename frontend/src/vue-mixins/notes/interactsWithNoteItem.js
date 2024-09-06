import FeatureSource from '@/components/features/FeatureSource.vue';

export default {
    components: {
        FeatureSource,
    },
    props: {
        note: {
            type: Object,
            required: true,
        },
        notebook: {
            type: [Object, null],
            default: null,
        },
    },
    computed: {
        name() {
            return this.note.name;
        },
        createdAt() {
            return this.note.createdAt;
        },
        preview() {
            return this.note.preview;
        },
        createdFormatted() {
            return this.$dayjs(this.createdAt).format('MMM D, LT');
        },
        featureItem() {
            return this.note;
        },
    },
};
