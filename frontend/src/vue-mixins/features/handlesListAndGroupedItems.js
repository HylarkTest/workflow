export default {
    computed: {

    },
    methods: {
        getConnectionKey(connectionArray) {
            return _.findKey(connectionArray, (__, key) => _.startsWith(key, '__'));
        },
        getConnection(connectionArray) {
            return connectionArray[this.getConnectionKey(connectionArray)];
        },
        getGroupings(itemResults, groupedItemResults) {
            if (this.groupPointer) {
                return groupedItemResults?.groups.map((items) => {
                    const connection = this.getConnection(items);
                    const groupHeader = connection.groupHeader;
                    const group = connection.group;
                    const pageInfo = connection.pageInfo;
                    return {
                        header: {
                            val: groupHeader,
                            group,
                            count: pageInfo.total,
                        },
                        items,
                    };
                });
            }
            return this.getBasicGroupings(itemResults);
        },
        getBasicGroupings(arr) {
            return [{
                header: { val: null },
                items: arr || [],
            }];
        },
        hasMoreToLoad(grouping, itemResults, groupedItemResults) {
            if (!this.groupPointer) {
                if (!itemResults) {
                    return false;
                }
                return this.getConnection(itemResults).pageInfo.hasNextPage;
            }
            const group = groupedItemResults.groups.find((items) => {
                return grouping.header.val === this.getConnection(items).group;
            });
            if (!group) {
                return false;
            }
            return this.getConnection(group).pageInfo.hasNextPage;
        },
    },
};
