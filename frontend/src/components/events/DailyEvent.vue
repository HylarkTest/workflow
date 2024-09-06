<template>
    <ButtonEl
        class="o-daily-event"
        :class="eventClass"
        :style="eventStyle"
        @click.stop="selectEvent"
    >
        <div
            class="o-daily-event__bar"
            :style="{ backgroundColor: textColor }"
        >
            &nbsp;
        </div>
        <div class="text-xs font-medium">
            {{ event.name }}
        </div>
    </ButtonEl>
</template>

<script>

import interactsWithEventItem from '@/vue-mixins/events/interactsWithEventItem.js';

import useEventItem from '@/composables/useEventItem.js';

export default {
    name: 'DailyEvent',
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
        actionProcessing: Boolean,
        deleteProcessing: Boolean,
    },
    setup(props, context) {
        const {
            textColor,
            bgColor,
            startDateTime,
            endDateTime,
            endTime,
            isAllDay,
            isSingleDay,
            isFirstDay,
            isLastDay,
            viewedCellDay,
            actionProcessingClass,
            deleteProcessingClass,
            selectEvent,
        } = useEventItem(props, context);

        return {
            textColor,
            bgColor,
            startDateTime,
            endDateTime,
            endTime,
            isAllDay,
            isSingleDay,
            isFirstDay,
            isLastDay,
            viewedCellDay,
            actionProcessingClass,
            deleteProcessingClass,
            selectEvent,
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
        eventStyle() {
            return {
                backgroundColor: this.bgColor,
                height: this.eventHeight,
            };
        },
        eventClass() {
            let classes = '';
            if (this.isAllDay || this.isLastDay) {
                classes = classes.concat('rounded-b-lg');
            }
            if (this.isAllDay || this.isFirstDay) {
                classes = classes.concat(' rounded-t-lg');
            }
            return classes;
        },
        eventHeight() {
            if (this.isAllDay) {
                return 'auto';
            }
            const number = 12 * this.durationInBlocks;
            return `${number}px`;
        },
        durationInMinutes() {
            return this.$dayjs(this.endOrEndOfDay).diff(this.startOrStartOfDay, 'minutes');
        },
        durationInBlocks() {
            return this.durationInMinutes / 12;
        },
        endsAtMidnight() {
            return this.endTime === '00:00';
        },
        endOrEndOfDay() {
            if (!this.endsAtMidnight && (this.isSingleDay || this.isLastDay)) {
                return this.endDateTime;
            }
            return `${this.viewedCellDay} 23:55:00`;
        },
        startOrStartOfDay() {
            if (this.isSingleDay || this.isFirstDay) {
                return this.startDateTime;
            }
            return `${this.viewedCellDay} 00:00:00`;
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

.o-daily-event {
    @apply
        flex
        px-2
        py-1.5
        relative
    ;

    &__bar {
        @apply
            h-full
            mr-2
            rounded-full
            w-1
        ;
    }
}

</style>
