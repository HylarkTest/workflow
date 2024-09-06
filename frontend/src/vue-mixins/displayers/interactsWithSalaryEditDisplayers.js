const periodOptions = [
    'HOURLY',
    'DAILY',
    'WEEKLY',
    'MONTHLY',
    'YEARLY',
    'ONE_TIME',
];

export default {
    computed: {
        salaryPeriod: {
            get() {
                return this.modifiableFieldValue?.period;
            },
            set(period) {
                this.updateDataValue(period, 'period');
            },
        },
        amount() {
            return this.modifiableFieldValue?.amount;
        },
    },
    created() {
        this.periodOptions = periodOptions;
        this.periodDisplay = (period) => this.$t(`labels.salaryPeriods.per.${_.camelCase(period)}`);
    },
};
