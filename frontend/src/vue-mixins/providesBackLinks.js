// import { getPreviousRoute } from '@/router.js';

export default {
    data() {
        return {
            backHistory: window.history.state.back,
        };
    },
    methods: {
        backLink(defaultLink = null) {
            if (this.backHistory) {
                this.$router.back();
            } else if (defaultLink) {
                this.$router.push({ name: defaultLink });
            } else {
                this.$router.push({ name: 'home' });
            }
        },
    },
};
