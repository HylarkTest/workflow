<template>
    <ButtonEl
        class="o-monthly-event"
        @click.stop="selectEvent"
    >
        <div
            v-if="!hasOverlay"
            class="o-monthly-event__main"
            :class="eventClass"
            :style="colorStyle"
        >
            <div
                class="text-xs font-medium u-ellipsis flex-1 mr-1"
            >
                {{ event.name }}
            </div>

            <div
                v-if="showStartTime"
                class="text-xxs text-cm-500 hidden md:block"
            >
                {{ startTimeFormatted }}
            </div>
        </div>

        <div
            v-else
            class="o-monthly-event__main o-monthly-event__overlay"
            :class="overlayClass"
            :style="overlayStyle"
        >
            <div class="flex items-center min-w-0">
                <div
                    class="text-xs font-medium u-ellipsis mr-2"
                    :style="{ color: textColor }"
                >
                    {{ event.name }}
                </div>

                <div
                    v-if="start && !isAllDay && isFirstDay"
                    class="text-xxs text-cm-500 shrink-0"
                >
                    {{ startTimeFormatted }}
                </div>
            </div>

            <div
                v-if="showEndDate"
                class="text-xxs text-cm-500 shrink-0"
            >
                Ends {{ endTimeFormatted }}
            </div>
        </div>
    </ButtonEl>
</template>

<script>

import interactsWithEventItem from '@/vue-mixins/events/interactsWithEventItem.js';

import useEventItem from '@/composables/useEventItem.js';

export default {
    name: 'MonthlyEvent',
    components: {

    },
    mixins: [
        interactsWithEventItem,
    ],
    props: {
        event: {
            type: Object,
            required: true,
        },
        cellDateObj: {
            type: [Object, null],
            default: null,
        },
        weekStart: {
            type: Number,
            required: true,
        },
        dayIndex: {
            type: Number,
            required: true,
        },
        actionProcessing: Boolean,
        deleteProcessing: Boolean,
    },
    setup(props, context) {
        const {
            textColor,
            bgColor,
            endDateTime,
            startTimeFormatted,
            endTimeFormatted,
            isAllDay,
            isSingleDay,
            isFirstDay,
            isLastDay,
            isMiddleDay,
            viewedCellDay,
            actionProcessingClass,
            deleteProcessingClass,
            selectEvent,
        } = useEventItem(props, context);

        return {
            textColor,
            bgColor,
            endDateTime,
            startTimeFormatted,
            endTimeFormatted,
            isAllDay,
            isSingleDay,
            isFirstDay,
            isLastDay,
            isMiddleDay,
            viewedCellDay,
            actionProcessingClass,
            selectEvent,
            deleteProcessingClass,
        };
    },
    emits: [
        'selectEvent',
    ],
    data() {
        return {

        };
    },
    computed: {
        start() {
            return this.event.date;
        },
        colorStyle() {
            return {
                backgroundColor: this.bgColor,
                color: this.textColor,
            };
        },
        showStartTime() {
            return this.start && !this.isAllDay;
        },
        eventClass() {
            return [this.spaceClass, this.firstClass, this.lastClass, this.shadowClass];
        },
        shadowClass() {
            return { shadow: this.isSingleDay };
        },
        spaceClass() {
            return { 'mx-1.5 rounded-lg': this.isSingleDay };
        },
        firstClass() {
            return this.isFirstDay ? 'pl-1.5 rounded-l-lg' : '';
        },
        lastClass() {
            return this.isLastDay ? 'pr-1.5 rounded-r-lg' : '';
        },
        hasOverlay() {
            return this.isFirstDay || (this.isFirstWeekday && !this.isSingleDay);
        },
        firstOfMiddleDay() {
            return this.isMiddleDay && this.isFirstWeekday;
        },
        isFirstWeekday() {
            return this.dayIndex === 0;
        },
        overlayClass() {
            return [this.overlayFirstClass, this.overlayLastClass];
        },
        overlayFirstClass() {
            return this.isFirstDay ? 'ml-1.5 rounded-l-lg' : '';
        },
        overlayLastClass() {
            return this.lastOverlay ? 'mr-1.5 rounded-r-lg' : '';
        },
        overlayWidth() {
            const percentage = 100 * this.functionalDuration;
            // const betweenDays = this.functionalDuration - 6; // Add a px each time the border is crossed
            let modifier = 0;
            if (this.lastOverlay) {
                modifier -= 5;
            }
            if (this.isFirstDay) {
                modifier -= 5;
            }
            return `calc(${percentage}% + ${modifier}px)`;
        },

        overlayStyle() {
            return {
                backgroundColor: this.bgColor,
                width: this.overlayWidth,
            };
        },
        differenceFromEnd() {
            return this.$dayjs(this.endDateTime).diff(this.viewedCellDay, 'day');
        },
        durationFromNow() {
            return this.isSingleDay ? 1 : this.differenceFromEnd + 1;
        },
        functionalDuration() {
            return this.remainingThisWeek;
        },
        usableMax() {
            if (this.durationFromNow <= this.remainingDaysInWeek) {
                return this.durationFromNow;
            }
            return this.remainingDaysInWeek;
        },
        remainingDaysInWeek() {
            // Including the current day
            return 7 - this.dayIndex;
        },
        remainingThisWeek() {
            return this.hasOverlay ? this.usableMax : 0;
        },
        showEndDate() {
            return !this.isSingleDay && this.isEndThisWeek && !this.isAllDay;
        },
        lastOverlay() {
            if (this.isLastDay) {
                return true;
            }
            if ((this.isFirstWeekday || this.isFirstDay) && this.isEndThisWeek) {
                return true;
            }
            return false;
        },
        isEndThisWeek() {
            return this.differenceFromEnd < this.remainingDaysInWeek;
        },
    },
    methods: {
    },
    created() {

    },
};
</script>

<style scoped>

.o-monthly-event {
    @apply
        relative
    ;

    &__main {
        @apply
            flex
            items-center
            justify-between
            px-1.5
            py-0.5
        ;
    }

    /*&--middle {
        margin-left: -1px;
        width: calc(100% + 1px);
    }

    &--last {
        margin-left: -1px;
    }*/

    &:not(:last-child) {
        margin-bottom:  4px;
    }

    &__overlay {
        @apply
            absolute
            left-0
            shadow
            top-0
            w-full
            z-over
        ;
    }
}

</style>
