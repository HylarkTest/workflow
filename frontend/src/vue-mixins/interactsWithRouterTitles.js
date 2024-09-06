import { setDocumentTitle } from '@/core/utils.js';

export default {
    computed: {
        routerTitle() {
            return null;
        },
    },
    mounted() {
        // in providesApolloFullItem.js we call this.$apollo.addSmartQuery('fullItem', { ... }) in created()
        // which means we cannot watch any computed functions that depend on the result of that query.
        // Adding this watcher on mount allows us to define the apollo query first.
        this.$watch(
            'routerTitle',
            (newTitle) => {
                if (this.allowRouterTitle) {
                    setDocumentTitle(newTitle);
                }
            },
            { immediate: true }
        );
    },
};
