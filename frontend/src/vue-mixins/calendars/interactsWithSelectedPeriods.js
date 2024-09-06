import {
    dayPositionInWeek,
} from '@/core/helpers/dateHelpers.js';

import {
    weekdayStart,
} from '@/core/repositories/preferencesRepository.js';

const periods = [
    'MONTHLY',
    'WEEKLY',
    'DAILY',
];

export default
{
    data() {
        return {
            selectedPeriod: periods[0],
            weekStart: weekdayStart,
        };
    },
    computed: {
        viewedMonth: {
            get() {
                return this.viewedFullObject.month();
            },
            set(month) {
                this.viewedFullObject = this.viewedFullObject.month(month);
            },
        },
        viewedYear: {
            get() {
                return this.viewedFullObject.year();
            },
            set(year) {
                this.viewedFullObject = this.viewedFullObject.year(year);
            },
        },
        viewedDay: {
            get() {
                return this.viewedFullObject.date();
            },
            set(day) {
                this.viewedFullObject = this.viewedFullObject.date(day);
            },
        },
        firstWeekday() {
            const difference = this.dayInWeek;
            return this.viewedFullObject.subtract(difference, 'day');
        },
        lastWeekday() {
            const difference = 6 - this.dayInWeek;
            return this.viewedFullObject.add(difference, 'day');
        },
        weeklyPeriod() {
            return [
                this.firstWeekday,
                this.lastWeekday,
            ];
        },
        dayInWeek() {
            return dayPositionInWeek(this.viewedYear, this.viewedMonth, this.viewedDay, this.weekStart);
        },
    },
    methods: {
        isSelectedPeriod(period) {
            return this.selectedPeriod === period;
        },
        periodString(period) {
            return this.$t(`common.dates.${_.camelCase(period)}`);
        },
        selectPeriod(period) {
            this.selectedPeriod = period;
        },

        // Selected date manipulation
        moveViewedDate(forward) {
            const periodMap = {
                MONTHLY: 'month',
                WEEKLY: 'week',
                DAILY: 'day',
            };
            const period = periodMap[this.selectedPeriod];
            const method = forward ? 'add' : 'subtract';
            this.viewedFullObject = this.viewedFullObject[method](1, period);
        },
        forwardOne() {
            return this.moveViewedDate(true);
        },
        backOne() {
            return this.moveViewedDate(false);
        },
    },
    created() {
        this.periods = periods;
    },
};
