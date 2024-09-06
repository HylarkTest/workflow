<template>
    <div
        v-blur="closePopup"
        class="c-time-with-picker"
    >
        <button
            ref="timeDisplay"
            :class="displayClasses"
            type="button"
            @click="togglePopup"
        >
            {{ timeDisplay }}
        </button>

        <component
            v-if="showPopup"
            ref="pickerPopup"
            :is="pickerComponent"
            containerClass="p-2"
            :viewedMonth="month"
            :viewedYear="year"
            :activator="$refs.timeDisplay"
            v-bind="$attrs"
            @selectYear="$emit('update:year', $event)"
            @selectMonth="$emit('update:month', $event)"
        >
        </component>
    </div>
</template>

<script>

import DatePickerPopup from '@/components/datePicker/DatePickerPopup.vue';
import MonthYearPopup from '@/components/datePicker/MonthYearPopup.vue';

export default {
    name: 'TimeWithPicker',
    components: {
        DatePickerPopup,
        MonthYearPopup,
    },
    mixins: [
    ],
    props: {
        displayClasses: {
            type: String,
            default: 'font-semibold',
        },
        month: {
            type: [Number, null],
            default: null,
        },
        year: {
            type: [Number, null],
            default: null,
        },
        day: {
            type: [Number, null],
            default: null,
        },
        displayFormat: {
            type: String,
            default: 'MONTH_YEAR',
            validator(val) {
                return ['MONTH_YEAR', 'DAY_MONTH_YEAR', 'WEEK_RANGE'].includes(val);
            },
        },
        weeklyPeriod: {
            type: [Array, null],
            default: null,
        },
    },
    emits: [
        'update:year',
        'update:month',
    ],
    data() {
        return {
            showPopup: false,
        };
    },
    computed: {
        pickerComponent() {
            return this.isMonthYear ? 'MonthYearPopup' : 'DatePickerPopup';
        },
        isMonthYear() {
            return this.displayFormat === 'MONTH_YEAR';
        },
        isWeekly() {
            return this.displayFormat === 'WEEK_RANGE';
        },
        timeDisplay() {
            const monthYear = `${this.$dayjs().month(this.month).format('MMMM')} ${this.year}`;
            if (this.isWeekly) {
                return this.weekDisplay;
            }
            if (this.displayFormat === 'DAY_MONTH_YEAR') {
                return `${this.day} ${monthYear}`;
            }
            return monthYear;
        },
        weekDisplay() {
            if (!this.isWeekly) {
                return false;
            }
            const startDate = this.weeklyPeriod[0];
            const endDate = this.weeklyPeriod[1];

            let start = _.toString(startDate.date());
            let end = _.toString(endDate.date());

            const startMonth = startDate.month();
            const endMonth = endDate.month();
            const endMonthFormat = endDate.format('MMM');

            const startYear = startDate.year();
            const endYear = endDate.year();

            if (startMonth !== endMonth) {
                start = start.concat(' ', startDate.format('MMM'));
            }
            end = end.concat(' ', endMonthFormat);

            if (startYear !== endYear) {
                start = start.concat(' ', startYear);
            }
            end = end.concat(' ', endYear);

            return `${start} - ${end}`;
        },
    },
    methods: {
        togglePopup() {
            this.showPopup = !this.showPopup;
        },
        closePopup() {
            this.showPopup = false;
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.c-time-with-picker {

} */

</style>
