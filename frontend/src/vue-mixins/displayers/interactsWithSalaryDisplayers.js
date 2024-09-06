export default {
    computed: {
        periodString() {
            const formatted = _.camelCase(this.displayFieldValue.period);
            return this.$t(`labels.salaryPeriods.${formatted}`);
        },
    },
};
