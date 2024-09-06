export default {
    data() {
        return {
            page: 1,
            pageSize: 10,
            queries: [],
        };
    },
    computed: {
        limit() {
            return this.pageSize * this.page;
        },
        hasMoreLoadedItems() {
            return !!this.remainingItems.length;
        },
        remainingItems() {
            return this.combinedItems.slice(this.limit);
        },
        allItems() {
            return _.take(this.combinedItems, this.limit);
        },
        hasMore() {
            if (this.hasMoreLoadedItems) {
                return true;
            }
            return _.some(this.queries, (query) => this.hasMoreForQuery(query));
        },
    },
    methods: {
        showMore() {
            const promises = [];
            _.forEach(this.queries, (query) => {
                if (this.hasMoreForQuery(query) && this.allVisibleForQuery(query)) {
                    promises.push(this.loadMoreForQuery(query));
                }
            });
            if (promises.length) {
                Promise.all(promises).then(() => {
                    this.page += 1;
                });
            } else if (this.hasMoreLoadedItems) {
                this.page += 1;
            }
        },
    },
};
