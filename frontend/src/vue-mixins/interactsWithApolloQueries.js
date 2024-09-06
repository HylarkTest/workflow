const LOADING_FIRST_TIME = 1;
// This doesn't actually work with Vue Apollo as it doesn't use the `setVariables` method.
// So queries loading from a change actually look like they are loading for the first time.
// const LOADING_FROM_CHANGE = 2;
const LOADING_MORE = 3;
const REFETCHING = 4;

export default {
    computed: {
        $isLoading() {
            return this.$apollo.loading;
        },
        $isRefetching() {
            return this.$isRefetchingQueries();
        },
        $isFetchingMore() {
            return this.$isFetchingMoreQueries();
        },
        $isLoadingFromChange() {
            return this.$isLoadingQueriesFromChange();
        },
        $isLoadingFirstTime() {
            return this.$isLoadingQueriesFirstTime();
        },
        $isLoadingNewOrFromChange() {
            return this.$isLoadingQueriesFirstTimeOrFromChange();
        },
        $isRefetchingOrFetchingMore() {
            return this.$isRefetchingOrFetchingMoreQueries();
        },
    },
    methods: {
        _getQueriesArray(queries) {
            if (!queries.length) {
                return Object.keys(this.$apollo.queries);
            }
            return queries;
        },
        _queryHasNetworkStatus(query, networkStatus) {
            return query.observer.queryInfo.networkStatus === networkStatus;
        },
        $getQuery(queryName) {
            return this.$apollo.queries[queryName];
        },
        $getQueryInfo(queryName) {
            return this.$apolloData.queries[queryName];
        },
        $isLoadingQueries(queries = [], loadingIfQueryMissing = false) {
            return this._getQueriesArray(queries).some((queryName) => {
                const query = this.$getQuery(queryName);
                if (!query) {
                    return loadingIfQueryMissing;
                }
                return query.loading;
            });
        },
        $isRefetchingQueries(queries = []) {
            return this.$isLoadingQueriesWithNetworkStatus(queries, REFETCHING);
        },
        $isFetchingMoreQueries(queries = []) {
            return this.$isLoadingQueriesWithNetworkStatus(queries, LOADING_MORE);
        },
        $isRefetchingOrFetchingMoreQueries(queries = []) {
            return this.$isRefetchingQueries(queries) || this.$isFetchingMoreQueries(queries);
        },
        $isLoadingQueriesFirstTime(queries = []) {
            // Assume that some queries have yet to be created in `created` or `mounted` lifecycle hooks
            if (this._areSomeQueriesMissing(queries)) {
                return true;
            }
            // If all the queries have data, then they are not loading for the first time even if they
            // have the `LOADING_FIRST_TIME` network status.
            if (this._isDataPresentForAllNonSkippedQueries(queries)) {
                return false;
            }
            return this.$isLoadingQueriesWithNetworkStatus(queries, LOADING_FIRST_TIME);
        },
        $isLoadingQueriesFromChange(queries) {
            // If there are missing queries, or if some queries don't have data then they are not
            // loading from a change.
            if (!this._isDataPresentForAllNonSkippedQueries(queries) || this._areSomeQueriesMissing(queries)) {
                return false;
            }
            return this.$isLoadingQueriesWithNetworkStatus(queries, LOADING_FIRST_TIME);
        },
        $isLoadingQueriesFirstTimeOrFromChange(queries, notLoadingFirstTimeIfDataPresent = true) {
            return this.$isLoadingQueriesFirstTime(queries, notLoadingFirstTimeIfDataPresent)
                || this.$isLoadingQueriesFromChange(queries);
        },
        $isLoadingQueriesWithNetworkStatus(queries, networkStatus) {
            return this._someQueries(queries, (query, _queryData, queryInfo) => {
                return (queryInfo.loading && this._queryHasNetworkStatus(query, networkStatus));
            });
        },
        _isDataPresentForAllNonSkippedQueries(queries) {
            const queryNames = this._getQueriesArray(queries);
            const nonSkippedQueries = queryNames.filter((name) => !this.$getQuery(name).skip);
            if (!nonSkippedQueries.length) {
                return false;
            }
            return nonSkippedQueries.every((name) => {
                const data = this[name];
                return !_.isNull(data) && !_.isUndefined(data);
            });
        },
        _areSomeQueriesMissing(queryNames) {
            const queriesArray = this._getQueriesArray(queryNames);
            return queryNames?.length
                ? queriesArray.length < queryNames.length
                : queriesArray.length === 0;
        },
        _someQueries(queries, callback) {
            const queriesArray = this._getQueriesArray(queries);

            return queriesArray.some((queryName) => {
                const query = this.$getQuery(queryName);
                const queryInfo = this.$getQueryInfo(queryName);
                const queryData = this[queryName];
                return callback(query, queryData, queryInfo);
            });
        },
    },
};
