import { timeFormat } from '@/core/repositories/preferencesRepository.js';

export default {
    data() {
        return {
            timeFormat,
        };
    },
    computed: {
        hours() {
            return _.range(24);
        },
        hoursFormatted() {
            return this.hours.map((hour) => {
                const obj = {
                    hour,
                    formatted: null,
                };

                if (this.timeFormat === '12') {
                    if (hour === 0) {
                        obj.formatted = '12:00 AM';
                    } else if (hour === 12) {
                        obj.formatted = '12:00 PM';
                    } else if (hour < 12) {
                        obj.formatted = `${hour}:00 AM`;
                    } else {
                        obj.formatted = `${hour - 12}:00 PM`;
                    }
                } else {
                    obj.formatted = `${_.padStart(hour, 2, '0')}:00`;
                }

                return obj;
            });
        },
    },
};
