<template>
    <div
        class="c-time-input"
        :class="{ unclickable: deactivated || timeOptions.forceAllDay }"
    >
        <div class="mr-3 text-cm-400 text-xs">
            <i class="fal fa-clock">
            </i>
        </div>

        <div class="flex items-center">
            <div class="c-time-input__selectors">
                <TimeOptions
                    v-model="handleHour"
                    :timeOptions="timeOptions"
                    :is24Hours="is24Hours"
                >
                </TimeOptions>

                <span class="mx-1">
                    :
                </span>

                <TimeOptions
                    ref="minutes"
                    v-model="handleMinute"
                    :timeOptions="timeOptions"
                    :is24Hours="is24Hours"
                    optionType="minutes"
                >
                </TimeOptions>
            </div>

            <div
                v-if="!is24Hours"
                class="c-time-input__meridiems"
            >
                <button
                    class="c-time-input__meridiem"
                    :class="{ 'bg-primary-600 text-cm-00': isSelectedMeridiem('am') }"
                    type="button"
                    @click="setMeridiem('am')"
                >
                    AM
                </button>

                <button
                    class="c-time-input__meridiem"
                    :class="{ 'bg-primary-600 text-cm-00': isSelectedMeridiem('pm') }"
                    type="button"
                    @click="setMeridiem('pm')"
                >
                    PM
                </button>
            </div>

            <ClearButton
                v-if="time && showClear"
                positioningClass="ml-2"
                @click.stop="updateModelValue(null)"
            >
            </ClearButton>
        </div>
    </div>
</template>

<script>
import TimeOptions from '@/components/datePicker/TimeOptions.vue';
import ClearButton from '@/components/buttons/ClearButton.vue';

import useDateTime from '@/composables/useDateTime.js';
import useTimeOptions from '@/composables/useTimeOptions.js';
import useTimeInput from '@/composables/useTimeInput.js';

export default {
    name: 'TimeInput',
    components: {
        TimeOptions,
        ClearButton,
    },
    props: {
        dateTime: {
            type: [String, Object, null],
            default: null,
        },
        timeOptionsProp: {
            type: Object,
            default: () => ({}),
        },
        noTimezone: Boolean,
        isMicrosoftItem: Boolean,
        deactivated: Boolean,
        showClear: Boolean,
    },
    emits: [
        'update:dateTime',
    ],
    setup(props, context) {
        const {
            currentTime,
        } = useDateTime(props);

        const {
            timeOptions,
        } = useTimeOptions(props);

        const {
            omitTime,
            modelValue,
            updateModelValue,
        } = useTimeInput(props, context);

        return {
            timeOptions,
            omitTime,
            currentTime,
            modelValue,
            updateModelValue,
        };
    },
    computed: {
        time() {
            return this.omitTime ? null : this.modelValue;
        },
        noTimeSet() {
            return !this.time;
        },
        currentHourInTimezone() {
            return this.currentTime?.split(':')[0] || null;
        },
        is24Hours() {
            return this.timeOptions.is24Hours;
        },
        hour() {
            return this.time?.split(':')[0] || null;
        },
        minute() {
            return this.time?.split(':')[1] || null;
        },
        handleHour: {
            get() {
                const hour = this.hour || null;

                if (_.isNull(hour)) {
                    return null;
                }
                if (!this.is24Hours && hour === '00') {
                    return '12';
                }
                if (this.is24Hours || (hour <= 12)) {
                    return hour;
                }
                return _.toString(hour - 12);
            },
            async set(hour) {
                this.setHour(hour, this.meridiem);
                await this.$nextTick();
                this.$refs.minutes?.openPopup();
            },
        },
        handleMinute: {
            get() {
                return this.minute;
            },
            set(minute) {
                const correctHour = this.hour || this.currentHourInTimezone;
                const time = `${correctHour}:${minute}:00`;
                this.updateModelValue(time);
            },
        },
        meridiem() {
            const hour = this.hour || this.currentHourInTimezone;
            return hour < 12 ? 'am' : 'pm';
        },
    },
    methods: {
        isSelectedMeridiem(meridiem) {
            if (this.omitTime) {
                return false;
            }
            return meridiem === this.meridiem;
        },
        setMeridiem(meridiem) {
            this.setHour(this.hour, meridiem);
        },
        setHour(hour, meridiem) {
            let correctHour = _.toNumber(hour || this.currentHourInTimezone);
            if (!this.is24Hours) {
                if (meridiem === 'am' && correctHour === 12) {
                    correctHour = '00';
                } else if (meridiem === 'am' && correctHour > 12) {
                    correctHour = _.min([correctHour - 12, 12]);
                } else if (meridiem === 'pm' && correctHour < 12) {
                    correctHour += 12;
                }
            } else if (correctHour > 23) {
                correctHour = 23;
            }
            const formattedHour = _.padStart(correctHour, 2, '0');
            const time = `${formattedHour}:${this.minute || '00'}:00`;
            this.updateModelValue(time);
        },
    },
};
</script>

<style scoped>

.c-time-input {
    min-width:  150px;

    @apply
        bg-cm-100
        flex
        items-center
        justify-between
        px-2
        py-1
        rounded-lg
    ;

    &__selectors {
        @apply
            flex
            items-center
            mr-2
        ;
    }

    &__meridiems {
        padding: 1px;
        @apply
            bg-primary-100
            rounded-md
            text-primary-600
            text-xs
        ;
    }

    &__meridiem {
        padding: 1px 3px;

        @apply
            rounded-md
        ;
    }
}

</style>
