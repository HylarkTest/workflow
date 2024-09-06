<template>
    <div class="c-calendar-picker">
        <div class="flex items-center justify-between mb-3">

            <div
                v-blur="closeMonths"
                class="flex-1 mr-2"
            >
                <ButtonEl
                    ref="monthInitiator"
                    class="c-calendar-picker__selector c-calendar-picker__month md:px-3 px-1.5"
                    type="button"
                    @click="viewDates"
                >
                    <div class="text-cm-00 text-xssm md:text-sm">
                        {{ $dayjs().month(viewedMonth).format('MMMM') }}
                    </div>

                    <div class="flex text-sm items-center">

                        <ButtonEl
                            v-if="notThisMonth"
                            class="mr-2"
                            @click.stop="goToThisMonth"
                        >
                            <HylarkSimplified
                                class="h-3 w-3 relative"
                                title="Go to today"
                            >
                            </HylarkSimplified>
                        </ButtonEl>

                        <button
                            class="c-calendar-picker__switch centered mr-1"
                            :class="{ unclickable: lastMinOption }"
                            type="button"
                            @click.stop="backMonthly"
                        >
                            <i class="far fa-angle-left">
                            </i>
                        </button>
                        <button
                            class="c-calendar-picker__switch centered"
                            :class="{ unclickable: lastMaxOption }"
                            type="button"
                            @click.stop="forwardMonthly"
                        >
                            <i class="far fa-angle-right">
                            </i>
                        </button>
                    </div>
                </ButtonEl>

                <MonthOptionsPopup
                    v-if="showDates"
                    :viewedMonth="monthKey"
                    :activator="$refs.monthInitiator"
                    nudgeDownProp="0.125rem"
                    @selectMonth="selectMonth"
                >
                </MonthOptionsPopup>
            </div>

            <YearViewedOptions
                :viewedYear="yearKey"
                :maxYear="maxYear"
                :minYear="minYear"
                @selectYear="selectYear"
            >
            </YearViewedOptions>
        </div>

        <div class="flex mb-2">
            <div
                v-for="day in weekdays"
                :key="day"
                class="c-calendar-picker__cell c-calendar-picker__weekday"
            >
                {{ $t('common.dates.days.' + day + '.short') }}
            </div>
        </div>

        <div class="flex flex-wrap text-sm">
            <PickerCell
                v-for="day in calendarRange"
                :key="day"
                class="c-calendar-picker__cell"
                :day="day"
                :displayOnly="displayOnly"
                :viewedMonth="viewedMonth"
                :selectedDate="modelValue"
                :colorName="colorName"
                :hasEvent="hasEvent(day)"
                :dateNullable="dateNullable"
                @selectDate="selectDate"
            >
            </PickerCell>
        </div>
    </div>
</template>

<script>

import MonthOptionsPopup from './MonthOptionsPopup.vue';
import YearViewedOptions from './YearViewedOptions.vue';
import PickerCell from './PickerCell.vue';
import HylarkSimplified from '@/components/branding/HylarkSimplified.vue';

import useDateInput from '@/composables/useDateInput.js';

import interactsWithWeekdays from '@/vue-mixins/calendars/interactsWithWeekdays.js';
import providesCalendarRange from '@/vue-mixins/calendars/providesCalendarRange.js';

export default {
    name: 'CalendarPicker',
    components: {
        MonthOptionsPopup,
        PickerCell,
        YearViewedOptions,
        HylarkSimplified,
    },
    mixins: [
        interactsWithWeekdays,
        providesCalendarRange,
    ],
    props: {
        dateTime: {
            type: [Object, String, null],
            default: null,
        },
        timeOptionsProp: {
            type: Object,
            default: () => ({}),
        },
        displayOnly: Boolean,
        displayedMonth: {
            type: [Number, null],
            default: null,
        },
        displayedYear: {
            type: [Number, null],
            default: null,
        },
        events: {
            type: [Array, null],
            default: null,
        },
        maxYear: {
            type: Number,
            default: 2030,
        },
        minYear: {
            type: Number,
            default: 1900,
        },
        colorName: {
            type: String,
            default: 'primary',
            validator: (color) => ['primary', 'secondary'].includes(color),
        },
        dateNullable: Boolean,
    },
    emits: [
        'update:dateTime',
        'update:displayedYear',
        'update:displayedMonth',
    ],
    setup(props, context) {
        const {
            modelValue,
            updateModelValue,
        } = useDateInput(props, context);

        return {
            modelValue,
            updateModelValue,
        };
    },
    data() {
        return {
            viewedMonth: new Date().getMonth(),
            viewedYear: new Date().getFullYear(),
            showDates: false,
        };
    },
    computed: {
        monthKey() {
            return this.displayedMonth || this.viewedMonth;
        },
        yearKey() {
            return this.displayedYear || this.viewedYear;
        },
        notThisMonth() {
            return (this.currentMonth !== this.monthKey)
                || (this.currentYear !== this.yearKey);
        },
        eventsRanges() {
            if (!this.events) {
                return null;
            }
            return _(this.events).flatMap((event) => {
                let start = this.$dayjs.tz(event.date, 'utc');
                let end = this.$dayjs.tz(event.end, 'utc');
                if (!event.isAllDay) {
                    start = utils.dateWithTz(start);
                    end = utils.dateWithTz(end);
                }
                return this.$dayjs().range(start, end, { unit: 'day' });
            }).value();
        },
        datesFormatted() {
            return this.eventsRanges?.map((event) => {
                return event.format('YYYY-MM-DD');
            });
        },
        uniqueDates() {
            return _.uniq(this.datesFormatted);
        },
        lastMinOption() {
            return (this.yearKey === this.minYear)
                && (this.viewedMonth === 0);
        },
        lastMaxOption() {
            return (this.yearKey === this.maxYear)
                && (this.viewedMonth === 11);
        },
    },
    methods: {
        hasEvent(day) {
            return this.uniqueDates.includes(day.format('YYYY-MM-DD'));
        },
        backMonthly() {
            this.closeMonths();
            if (this.viewedMonth === 0) {
                this.viewedMonth = 11;
                this.viewedYear -= 1;
                this.emitDisplayed('year', this.viewedYear);
                this.emitDisplayed('month', this.viewedMonth);
            } else {
                this.viewedMonth -= 1;
                this.emitDisplayed('month', this.viewedMonth);
            }
        },
        forwardMonthly() {
            this.closeMonths();
            if (this.viewedMonth === 11) {
                this.viewedMonth = 0;
                this.viewedYear += 1;
                this.emitDisplayed('year', this.viewedYear);
                this.emitDisplayed('month', this.viewedMonth);
            } else {
                this.viewedMonth += 1;
                this.emitDisplayed('month', this.viewedMonth);
            }
        },
        viewDates() {
            this.showDates = !this.showDates;
        },
        selectYear(year) {
            this.viewedYear = year;
            this.emitDisplayed('year', this.viewedYear);
        },
        selectMonth(month) {
            // Month is a number 0-11, 0: Jan, 1: Feb, 11: Dec, etc...
            this.viewedMonth = month;
            this.closeMonths();
            this.emitDisplayed('month', this.viewedMonth);
        },
        closeMonths() {
            this.showDates = false;
        },
        goToThisMonth() {
            this.closeMonths();
            this.viewedMonth = this.currentMonth;
            this.viewedYear = this.currentYear;
            this.emitDisplayed('year', this.viewedYear);
            this.emitDisplayed('month', this.viewedMonth);
        },
        selectDate(date) {
            this.updateModelValue(date);
        },
        emitDisplayed(period, val) {
            if (period === 'year') {
                this.$emit('update:displayedYear', val);
            } else {
                this.$emit('update:displayedMonth', val);
            }
        },
        setViews(date) {
            this.viewedYear = date.year();
            this.viewedMonth = date.month();
        },
    },
    watch: {
        modelValue(newDate) {
            if (newDate) {
                const date = this.$dayjs(newDate);
                this.setViews(date);
            }
        },
    },
    created() {
        if (this.modelValue) {
            const date = this.$dayjs(this.modelValue);
            this.setViews(date);
        }
    },
};
</script>

<style scoped>

.c-calendar-picker {
    /*So that the picker does not jump when clicking through months
    or different options*/
    height: 226px;

    &__selector {
        @apply
            bg-primary-800
            font-semibold
            py-1
            rounded-lg
        ;
    }

    &__month {
        @apply
            flex
            items-center
            justify-between
            w-full
        ;
    }

    &__switch {
        height:  15px;
        transition: 0.3s ease-in-out;
        width: 15px;

        @apply
            bg-cm-00
            rounded-md
            text-primary-600
        ;

        &:hover {
            @apply
                bg-primary-200
            ;
        }

        @media (min-width: 768px) {
            height: 19px;
            width: 19px;
        }
    }

    &__weekday {
        @apply
            font-semibold
            text-cm-400
            text-xs
        ;
    }

    &__cell {
        width: 14.28%;

        @apply
            text-center
        ;
    }
}

</style>
