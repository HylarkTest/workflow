export default {
    props: {
        isPreview: Boolean,
        isPlaceholder: Boolean,
        dataMap: {
            type: Object,
            default: null,
        },
        breakpointClass: {
            type: String,
            default: '',
        },
    },
};
