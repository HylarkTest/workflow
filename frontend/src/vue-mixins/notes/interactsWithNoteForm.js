export default {
    components: {
    },
    props: {
        notebook: {
            type: [Object, null],
            default: null,
        },
    },
    data() {
        return {
            featureType: 'NOTES',
            listKey: 'notebookId',
        };
    },
    watch: {
        // When prop changes in a list
        notebookId(id) {
            if (id) {
                this.form.notebookId = id;
            }
        },
    },
};
