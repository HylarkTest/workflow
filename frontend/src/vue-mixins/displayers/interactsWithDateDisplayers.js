export default {
    computed: {
        time() {
            return this.$dayjs(this.dataValue).utc().format('HH:mm');
        },
        isIgnoreTime() {
            return this.time === '23:59';
        },
        inTimezone() {
            if (this.isIgnoreTime) {
                return this.dataValue;
            }
            return utils.dateWithTz(this.dataValue);
        },
        dateTime() {
            if (this.isIgnoreTime) {
                return this.$dayjs(this.inTimezone).format('ll');
            }
            return this.$dayjs(this.inTimezone).format('lll');
        },
    },
};
