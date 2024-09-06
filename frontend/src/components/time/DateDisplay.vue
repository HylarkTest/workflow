<template>
    <div class="c-date-display flex items-center">
        <i
            class="fal mr-1.5"
            :class="dateIcon"
        >
        </i>

        <template
            v-if="modelValue"
        >
            {{ dateLabel }}
        </template>

        <span
            v-if="showPrompt"
            v-t="promptPath"
        >
        </span>

        <ClearButton
            v-if="modelValue && showClear"
            positioningClass="relative ml-2"
            @click.stop="$emit('clearDate')"
        >
        </ClearButton>

        <slot>
        </slot>
    </div>
</template>

<script>
import ClearButton from '@/components/buttons/ClearButton.vue';

import {
    isValidDate,
    formatDateTime,
    isToday,
    getBasicDateLabels,
} from '@/core/dateTimeHelpers.js';

import useDateTime from '@/composables/useDateTime.js';
import useTimezone from '@/composables/useTimezone.js';

export default {
    name: 'DateDisplay',
    components: {
        ClearButton,
    },
    mixins: [
    ],
    props: {
        dateTime: {
            type: [String, Object],
            required: true,
            validator: (date) => isValidDate(date),
        },
        dateFormat: {
            type: String,
            default: 'DATE_TIME',
        },
        promptPath: {
            type: [String, null],
            default: null,
        },
        showClear: Boolean,
        isMicrosoftItem: Boolean,
        noTimezone: Boolean,
    },
    emits: [
        'clearDate',
    ],
    setup(props) {
        const {
            modelValue,
            inputMode,
            isAllDay,
        } = useDateTime(props);

        const {
            timezone,
        } = useTimezone(props);

        return {
            modelValue,
            inputMode,
            timezone,
            isAllDay,
        };
    },
    computed: {
        showPrompt() {
            return this.promptPath && !this.dateTime;
        },
        timeFormat() {
            return utils.timeDayjsFormat();
        },
        formatType() {
            if (this.inputMode === 'DATE' || this.isAllDay) {
                return 'll';
            }
            if (this.inputMode === 'TIME') {
                return this.timeFormat;
            }
            return `ll ${this.timeFormat}`;
        },
        dateFormatted() {
            return formatDateTime(this.modelValue, this.formatType, this.timezone);
        },
        timeFormatted() {
            return formatDateTime(this.modelValue, this.timeFormat, this.timezone);
        },
        dateIcon() {
            return isToday(this.modelValue) ? 'fa-sun' : 'fa-calendar-day';
        },
        dateLabel() {
            const label = getBasicDateLabels(this.modelValue);

            if (label) {
                if (this.isAllDay) {
                    return label;
                }
                return `${label}, ${this.timeFormatted}`;
            }
            return this.dateFormatted;
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.c-date-display {

} */

</style>
