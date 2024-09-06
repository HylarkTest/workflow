import { weekdayStart } from '@/core/repositories/preferencesRepository.js';

const weekdaysList = [0, 1, 2, 3, 4, 5, 6];

export default {
    data() {
        return {
            // weekStart: 0,
        };
    },
    computed: {
        weekStart() {
            return weekdayStart.value;
        },
        weekdays() {
            return weekdaysList.map((day) => (day + this.weekStart) % 7);
        },
    },
    created() {
    },
};
