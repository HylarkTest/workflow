export default {
    props: {
    },
    computed: {
        title() {
            return this.article.title;
        },
        url() {
            return this.article.friendlyUrl;
        },
        topics() {
            return this.article?.topics;
        },
        topicsLength() {
            return this.topics?.length;
        },
        createdAt() {
            return this.article.createdAt;
        },
        updatedAt() {
            return this.article.updatedAt;
        },
        liveAt() {
            return this.article.liveAt;
        },
    },
};
