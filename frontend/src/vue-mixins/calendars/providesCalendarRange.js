// Requires viewedYear and viewedMonth as props or data

export default {
    data() {
        return {
            currentDate: new Date(),
        };
    },
    computed: {
        firstDateOfMonth() {
            // Return dayjs date object of first date the viewed month
            return this.$dayjs().year(this.viewedYear).month(this.viewedMonth).date(1);
        },
        daysInMonth() {
            // Number of days in the viewed month
            return this.$dayjs(this.firstDateOfMonth).daysInMonth();
        },
        currentMonth() {
            return this.currentDate.getMonth();
        },
        currentYear() {
            return this.currentDate.getFullYear();
        },
        firstDayInMonth() {
            // Returns a number between 0 and 6 corresponding to the weekday the first date falls on
            return (this.firstDateOfMonth.day() + (7 - this.weekStart)) % 7;
        },
        calendarStart() {
            // Take the days before this month starts from the previous month
            return this.firstDateOfMonth.clone().subtract(this.firstDayInMonth, 'days');
        },
        lastDateOfMonth() {
            const monthDays = this.daysInMonth - 1; // To remove current date
            return this.firstDateOfMonth.clone().add(monthDays, 'days');
        },
        lastDayInMonth() {
            return (this.lastDateOfMonth.day() + (7 - this.weekStart)) % 7;
        },
        calendarEnd() {
            // 6 is not week start, it is the last index of the week array
            const nextMonthDays = 6 - this.lastDayInMonth;
            return this.lastDateOfMonth.clone().add(nextMonthDays, 'days');
        },
        daysRange() {
            return this.calendarEnd.diff(this.calendarStart, 'day');
        },
        calendarRange() {
            return this.$dayjs().range(this.calendarStart, this.calendarEnd);
        },
    },
};
