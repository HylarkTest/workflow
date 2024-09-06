export default {
    props: {
        pages: {
            type: Array,
            required: true,
        },
    },
    computed: {
        pagesOptions() {
            return _.map(this.pages, 'id');
        },
    },
    methods: {
        initPageDisplay(nameKey) {
            this.pagesOptionsDisplay = (id) => {
                // Call pageDisplay with name key for display array
                const name = _.isFunction(nameKey) ? nameKey() : nameKey;
                return _(this.pages).find({ id })[name];
            };
        },
    },
};
