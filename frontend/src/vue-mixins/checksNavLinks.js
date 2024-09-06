export default {
    methods: {
        getLink(link, linkKey = 'link') {
            if (_.has(link, linkKey)) {
                return { name: link[linkKey] };
            }
            if (_.endsWith(link.__typename, 'Page')) {
                return link.route;
            }
            if (link.val) {
                return { name: _.lowerCase(link.val) };
            }
            return {};
        },
        isOnLink(link, linkKey = 'link') {
            if (link.val) {
                return this.$route.name === _.lowerCase(link.val);
            }
            if (!this.isLink(link, linkKey)) {
                return false;
            }
            if (_.has(link, linkKey)) {
                return this.$route.name === link[linkKey];
            }
            return this.$route.params.pageId === link.id;
        },
        isLink(link, linkKey = 'link') {
            return _.has(link, linkKey) || _.endsWith(link.__typename, 'Page');
        },
    },
};
