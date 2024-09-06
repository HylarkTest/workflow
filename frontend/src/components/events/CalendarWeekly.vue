<template>
    <div class="o-calendar-weekly">

        <Teleport
            :to="teleportRef"
        >
            <div
                class="o-calendar-weekly__days"
            >
                <div class="o-events-calendar__days">
                    <div
                        v-for="day in range"
                        :key="day.format()"
                        class="o-events-calendar__seventh o-events-calendar__weekday flex justify-center"
                    >
                        {{ $t('common.dates.days.' + day.day() + '.short') }}

                        <div
                            class="relative"
                            :class="dateClass(day)"
                        >
                            <HylarkSimplified
                                v-if="isToday(day)"
                                class="o-calendar-weekly__brand"
                            ></HylarkSimplified>

                            <div class="relative w-4">
                                {{ day.date() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <WeekDayRange
            :range="range"
            :events="events"
            :showCurrentTime="true"
            @selectEvent="$emit('selectEvent', $event)"
            @newEvent="$emit('newEvent', $event)"
        >
        </WeekDayRange>
    </div>
</template>

<script>

import WeekDayRange from './WeekDayRange.vue';
import HylarkSimplified from '@/components/branding/HylarkSimplified.vue';

export default {
    name: 'CalendarWeekly',
    components: {
        WeekDayRange,
        HylarkSimplified,
    },
    mixins: [
    ],
    props: {
        weeklyPeriod: {
            type: Array,
            required: true,
        },
        events: {
            type: [Array, null],
            default: null,
        },
        teleportRef: {
            type: [Object, null],
            default: null,
        },
    },
    emits: [
        'selectEvent',
        'newEvent',
    ],
    data() {
        return {

        };
    },
    computed: {
        range() {
            return this.$dayjs().range(this.weeklyPeriod[0], this.weeklyPeriod[1]);
        },
    },
    methods: {
        isToday(day) {
            return day.isToday();
        },
        dateClass(day) {
            return this.isToday(day) ? 'text-cm-00 font-semibold text-xssm ml-2' : 'ml-1';
        },
    },
    created() {

    },
};
</script>

<style scoped>

.o-calendar-weekly {
    --width: 80px;

    @apply
        flex
        flex-col
        min-h-0
    ;

    &__days {
        /*Cannot use var due to teleport*/
        margin-left: 80px;
    }

    &__hours {
        width: var(--width);

        @apply
            mr-4
        ;
    }

    &__brand {
        height: 23px;
        right: -6px;
        top: -3px;
        width: 23px;

        @apply
            absolute
            z-0
        ;
    }
}

</style>
