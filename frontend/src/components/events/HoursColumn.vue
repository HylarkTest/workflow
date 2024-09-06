<template>
    <div class="o-hours-column">
        <div
            v-for="hour in hoursFormatted"
            :key="hour.hour"
            class="o-hours-column__hour"
        >
            <template
                v-if="!(isCurrentHour(hour.hour) && hideNumber(hour.hour))"
            >
                {{ hour.formatted }}
            </template>

            <div
                class="o-hours-column__bar"
            >

            </div>

            <div
                v-if="showCurrentTime && isCurrentHour(hour.hour)"
                ref="currentTimeIndicator"
                class="o-hours-column__now"
                :style="{ marginTop: minuteMargin + 'px' }"
            >
                <div class="text-primary-600 text-xxs font-semibold">
                    {{ currentTimeFormatted }}
                </div>

                <div class="rounded-full h-2 w-2 bg-primary-600 ml-1">

                </div>

                <div class="o-hours-column__indicator">
                    &nbsp;
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import interactsWithAutoScroll from '@/vue-mixins/interactsWithAutoScroll.js';
import interactsWithHours from '@/vue-mixins/calendars/interactsWithHours.js';

export default {
    name: 'HoursColumn',
    components: {

    },
    mixins: [
        interactsWithAutoScroll,
        interactsWithHours,
    ],
    props: {
        showCurrentTime: Boolean,
    },
    data() {
        return {
            currentDate: this.$dayjs(),
        };
    },
    computed: {
        currentTzDate() {
            return utils.dateWithTz(this.currentDate);
        },
        currentTimeFormatted() {
            return utils.formattedTime(this.currentDate);
        },
        currentTime() {
            return this.currentTzDate.format('H:m');
        },
        currentHour() {
            const hour = this.currentTime.split(':')[0];
            return parseInt(hour, 10);
        },
        currentMinutes() {
            const minutes = this.currentTime.split(':')[1];
            return parseInt(minutes, 10);
        },
        minuteMargin() {
            const fractionOfHour = this.currentMinutes / 60;
            const proportionOfSpace = fractionOfHour * 60;
            const adjusted = proportionOfSpace - 17;
            return Math.round(adjusted);
        },
        hideBeforeMinutes() {
            return this.currentMinutes > 48;
        },
        hideAfterMinutes() {
            return this.currentMinutes < 12;
        },
    },
    methods: {
        isCurrentHour(hour) {
            return hour === this.currentHour;
        },
        isHourBefore(hour) {
            return hour === this.currentHour - 1;
        },
        hideBefore(hour) {
            return this.isHourBefore(hour) && this.hideBeforeMinutes;
        },
        hideNumber(hour) {
            return this.showCurrentTime && (this.hideBefore(hour) || this.hideAfterMinutes);
        },
    },
    created() {
        if (this.showCurrentTime) {
            this.interval = window.setInterval(() => {
                this.currentDate = this.$dayjs();
            }, 6000);
        }
    },
    mounted() {
        this.$nextTick(() => {
            this.scrollToElement(this.$refs.currentTimeIndicator[0]);
        });
    },
    unmounted() {
        if (this.showCurrentTime) {
            window.clearInterval(this.interval);
        }
    },
};
</script>

<style scoped>

.o-hours-column {
    margin-bottom: 7px;

    @apply
        text-cm-500
        text-xs
    ;

    &__hour {
        height: 60px;
    }

    &__bar {
        height: 1px;
        margin-left: 80px;
        margin-top: -10px;
        width: calc(100% - 80px);

        @apply
            absolute
            bg-cm-200
        ;
    }

    &__now {
        @apply
            absolute
            flex
            items-center
            w-full
            z-over
        ;
    }

    &__indicator {
        height: 2px;

        @apply
            bg-primary-600
            flex-1
        ;
    }
}

</style>
