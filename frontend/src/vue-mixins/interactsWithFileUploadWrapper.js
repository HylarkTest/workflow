export default {
    props: {
        formUrl: {
            type: String,
            default: '',
        },
        formFilename: {
            type: String,
            default: '',
        },
    },
    data() {
        return {
            file: null,
            url: this.formUrl,
            filename: this.formFilename,
            mimeType: '',
        };
    },
    methods: {
        removeFileFromData() {
            this.url = '';
            this.filename = '';
            this.mimeType = '';
            this.file = null;
        },
    },
    computed: {
    },
    watch: {
        file(value) {
            if (_.isNull(value)) {
                this.removeFileFromData();
            }
        },
    },
};
