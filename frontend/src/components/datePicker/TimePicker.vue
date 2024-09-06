<template>
    <div
        class="c-time-picker"
        :class="{ unclickable: deactivated }"
    >
        <div class="mr-3 text-cm-400 text-xs">
            <i
                class="fal fa-clock"
            >
            </i>
        </div>

        <div class="flex items-center">
            <div class="c-time-picker__selectors">
                <TimeOptions
                    v-model="hour"
                    :timeOptions="timeOptions"
                    :is24Hours="is24Hours"
                >
                </TimeOptions>

                <span class="mx-1">
                    :
                </span>

                <TimeOptions
                    ref="minutes"
                    v-model="minute"
                    :timeOptions="timeOptions"
                    :is24Hours="is24Hours"
                    optionType="minutes"
                >
                </TimeOptions>
            </div>

            <!-- Only if 12 hour clock vs 24 hour clock -->
            <div
                v-if="!is24Hours"
                class="c-time-picker__meridiems"
                :class="{ unclickable: noTimeSet }"
            >
                <button
                    class="c-time-picker__meridiem"
                    :class="{ 'bg-primary-600 text-cm-00': isSelectedMeridiem('am') }"
                    type="button"
                    @click="setMeridiem('am')"
                >
                    AM
                </button>

                <button
                    class="c-time-picker__meridiem"
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
                @click.stop="$emit('update:time', null)"
            >
            </ClearButton>
        </div>
    </div>
</template>

<script>

import TimeOptions from './TimeOptions.vue';
import ClearButton from '@/components/buttons/ClearButton.vue';

import {
    is24Hours,
} from '@/core/repositories/preferencesRepository.js';

export default {
    name: 'TimePicker',
    components: {
        TimeOptions,
        ClearButton,
    },
    mixins: [
    ],
    props: {
        // This component is unaffected by timezones. The value in data is the same as what is shown to the user.
        // If you wish to use this component within the context of timezones, pass the CONVERTED time to this component.
        time: {
            type: [String, null],
            default: null,
        },
        timeOptions: {
            type: Object,
            required: true,
        },
        showClear: Boolean,
        deactivated: Boolean,
    },
    emits: [
        'update:time',
    ],
    data() {
        return {
            is24Hours,
        };
    },
    computed: {
        omitTime() {
            return this.timeOptions.omitTime;
        },
        noTimeSet() {
            return !this.time || this.omitTime;
        },
        hour: {
            get() {
                // Display hour, so converted to 1-12 for 12 hour clock
                if (this.noHour) {
                    return null;
                }
                if (!this.is24Hours && this.hourTime === '00') {
                    return '12';
                }
                if (this.is24Hours || (this.hourTime <= 12)) {
                    return this.hourTime;
                }
                return _.toString(this.hourTime - 12);
            },
            async set(hour) {
                this.setHour(hour, this.meridiem);
                await this.$nextTick();
                this.$refs.minutes?.openPopup();
            },
        },
        noHour() {
            return !this.hourTime || this.omitTime;
        },
        noMinute() {
            return !this.minuteTime || this.omitTime;
        },
        hourTime() {
            return this.time?.split(':')[0];
        },
        minuteTime() {
            return this.time?.split(':')[1];
        },
        minute: {
            get() {
                if (this.noMinute || this.noTimeSet) {
                    return null;
                }
                return this.minuteTime;
            },
            set(minute) {
                let time;
                if (this.noHour) {
                    const thisHour = new Date().getHours();
                    time = `${thisHour}:${minute}`;
                } else {
                    time = `${this.hourTime}:${minute}`;
                }
                this.$emit('update:time', time);
            },
        },
        meridiem() {
            const hour = this.noTimeSet ? new Date().getHours() : this.hourTime;
            if (hour < 12) {
                return 'am';
            }
            return 'pm';
        },
    },
    methods: {
        isSelectedMeridiem(meridiem) {
            return meridiem === this.meridiem;
        },
        setMeridiem(meridiem) {
            this.setHour(this.hour, meridiem);
        },
        setHour(hour, meridiem) {
            let correctHour;
            if (!this.is24Hours) {
                if (meridiem === 'am' && _.toNumber(hour) === 12) {
                    correctHour = '00';
                } else if (meridiem === 'pm' && _.toNumber(hour) !== 12) {
                    correctHour = _.toString((_.toNumber(hour) + 12));
                } else {
                    correctHour = hour;
                }
            } else {
                correctHour = hour;
            }
            let time;
            if (this.noMinute) {
                time = `${correctHour}:00`;
            } else {
                time = `${correctHour}:${this.minuteTime}`;
            }
            this.$emit('update:time', time);
        },
    },
    created() {
    },
};
</script>

<style scoped>

.c-time-picker {
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
