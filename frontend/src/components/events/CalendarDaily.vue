<template>
    <div class="o-calendar-daily">

        <component
            :is="teleportRef ? 'Teleport' : 'div'"
            :to="teleportRef"
        >
            <div
                class="o-calendar-daily__days"
            >
                <div class="o-events-calendar__days">
                    <div
                        class="o-events-calendar__weekday flex justify-center w-full"
                    >
                        {{ $t('common.dates.days.' + day + '.full') }}

                        <div
                            class="relative"
                            :class="dateClass"
                        >
                            <HylarkSimplified
                                v-if="isToday"
                                class="o-calendar-daily__brand"
                            ></HylarkSimplified>

                            <div class="relative w-4">
                                {{ date }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </component>

        <WeekDayRange
            :range="range"
            :showCurrentTime="true"
            :events="events"
            @newEvent="$emit('newEvent', $event)"
            @selectEvent="$emit('selectEvent', $event)"
        >
        </WeekDayRange>
    </div>
</template>

<script>

import WeekDayRange from './WeekDayRange.vue';
import HylarkSimplified from '@/components/branding/HylarkSimplified.vue';

export default {
    name: 'CalendarDaily',
    components: {
        WeekDayRange,
        HylarkSimplified,
    },
    mixins: [
    ],
    props: {
        viewedFullObject: {
            type: Object,
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
        day() {
            return this.viewedFullObject.day();
        },
        date() {
            return this.viewedFullObject.date();
        },
        range() {
            return [this.viewedFullObject];
        },
        isToday() {
            return this.viewedFullObject.isToday();
        },
        dateClass() {
            return this.isToday ? 'text-cm-00 font-semibold text-xssm ml-2' : 'ml-1';
        },
    },
    methods: {
    },
    created() {

    },
};
</script>

<style>

.o-calendar-daily {
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
