export default {
    data() {
        return {
        };
    },
    computed: {
    },

    methods: {
        openObject(routeName, obj = null) {
            if (!obj) {
                this.$router.push({ name: `${routeName}.create` });
            } else {
                this.$router.push({ name: `${routeName}.edit`, params: { mapping: obj, id: obj.id } });
            }
        },
    },
};
