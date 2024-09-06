export default {
    computed: {
        format() {
            return utils.timeDayjsFormat();
        },
    },
    methods: {
        formatTheTime(time) {
            const timeCustomFormat = this.$dayjs(time, 'HH:mm');
            return timeCustomFormat.format(this.format);
        },
    },
};
