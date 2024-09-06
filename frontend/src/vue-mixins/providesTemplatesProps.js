export default {
    props: {
        container: {
            type: Object,
            required: true,
        },
        dataValue: {
            type: [Array, Number, String, Object, Boolean, null],
            default: null,
        },
    },
};
