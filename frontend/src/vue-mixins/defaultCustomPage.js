export default {
    computed: {
        generatesDefaultPage() {
            if (!this.page) {
                return {};
            }
            return null;
        },
    },
};
