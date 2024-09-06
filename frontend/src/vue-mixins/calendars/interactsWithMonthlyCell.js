export default {
    props: {
        day: {
            type: Object,
            required: true,
        },
        viewedMonth: {
            type: Number,
            required: true,
        },
    },
    computed: {
        dateColor() {
            if (this.isToday) {
                return 'text-cm-00';
            }
            if (this.viewedMonth === this.day.month()) {
                return 'text-cm-700';
            }
            return 'text-cm-300';
        },
        isToday() {
            const today = new Date();
            return this.day.year() === today.getFullYear()
                && this.day.month() === today.getMonth()
                && this.day.date() === today.getDate();
        },
    },
};
