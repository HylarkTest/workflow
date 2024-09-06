import FeatureSource from '@/components/features/FeatureSource.vue';
import DownloadButton from '@/components/buttons/DownloadButton.vue';

const icons = {
    docx: 'fa-file-word',
    pdf: 'fa-file-pdf',
    png: 'fa-image',
    jpeg: 'fa-image',
    jpg: 'fa-image',
};

export default {
    components: {
        FeatureSource,
        DownloadButton,
    },
    props: {
        document: {
            type: Object,
            required: true,
        },
        drive: {
            type: [Object, null],
            default: null,
        },
    },
    computed: {
        name() {
            return this.document.filename;
        },
        size() {
            return this.document.size;
        },
        formattedSize() {
            const size = this.size;
            if (size < 1000) {
                return `${size} B`;
            }
            if (size < 1000 * 1000) {
                return `${Math.round(size / 1000)} KB`;
            }
            if (size < 1000 * 1000 * 1000) {
                return `${Math.round((size / (1000 * 1000)) * 10) / 10} MB`; // Multiply by 10 to get 1 decimal place
            }
            return `${Math.round((size / (1000 * 1000 * 1000)) * 10) / 10} GB`;
        },
        icon() {
            return icons[this.extension] || 'fa-file';
        },
        extension() {
            return this.document.extension;
        },
        url() {
            return this.document.downloadUrl;
        },
        featureItem() {
            return this.document;
        },
    },
};
