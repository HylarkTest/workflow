<template>
    <div class="o-events-calendar">

        <Teleport
            v-if="teleportRef"
            :to="teleportRef"
        >
            <div
                class="o-events-calendar__panel pb-4"
            >
                <div
                    class="flex justify-between items-start mb-4 flex-col-reverse sm:flex-row"
                >
                    <div class="o-events-calendar__periods justify-center w-full sm:w-auto">
                        <button
                            v-for="period in periods"
                            :key="period"
                            class="o-events-calendar__period"
                            :class="selectedClasses(period)"
                            type="button"
                            @click="selectPeriod(period)"
                        >
                            {{ periodString(period) }}
                        </button>
                    </div>

                    <div class="flex items-center">
                        <div class="sm:text-right">
                            <div class="font-bold text-xl text-primary-900 leading-snug">
                                {{ currentTime }}
                            </div>
                            <div class="text-sm text-cm-600">
                                {{ todayFormatted }}
                            </div>
                        </div>
                        <BirdImage
                            class="h-16 ml-3"
                            whichBird="FlyingBird_72dpi.png"
                        >
                        </BirdImage>
                    </div>
                </div>

                <div class="flex justify-between items-center">
                    <TimeWithPicker
                        v-model:month="viewedMonth"
                        v-model:year="viewedYear"
                        :day="viewedDay"
                        :dateVal="viewedFullObject"
                        :weeklyPeriod="weeklyPeriod"
                        :displayFormat="currentPeriodDisplay"
                        displayClasses="text-sm sm:text-lg font-semibold hover:text-primary-600 transition-2eio"
                        @update:dateVal="setViewedDate"
                    >
                    </TimeWithPicker>
                    <div class="flex items-center">
                        <ButtonEl
                            v-if="isNotToday"
                            class="mr-3"
                            title="Go to today"
                            @click="goToToday"
                        >
                            <HylarkSimplified
                                class="h-5 w-5 relative"
                            >
                            </HylarkSimplified>
                        </ButtonEl>

                        <button
                            class="o-events-calendar__switch centered mr-2"
                            type="button"
                            @click="backOne"
                        >
                            <i class="fal fa-angle-left">
                            </i>
                        </button>
                        <button
                            class="o-events-calendar__switch centered"
                            type="button"
                            @click="forwardOne"
                        >
                            <i class="fal fa-angle-right">
                            </i>
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <component
            :is="calendarComponent"
            :viewedYear="viewedYear"
            :viewedMonth="viewedMonth"
            :viewedDay="viewedDay"
            :weeklyPeriod="weeklyPeriod"
            :viewedFullObject="viewedFullObject"
            :events="allEvents || []"
            :teleportRef="teleportRef"
            v-bind="$attrs"
            @selectEvent="selectEvent"
        >
        </component>
    </div>
</template>

<script>

import CalendarMonthly from './CalendarMonthly.vue';
import CalendarWeekly from './CalendarWeekly.vue';
import CalendarDaily from './CalendarDaily.vue';
import TimeWithPicker from '@/components/time/TimeWithPicker.vue';
import HylarkSimplified from '@/components/branding/HylarkSimplified.vue';

import interactsWithSelectedPeriods from '@/vue-mixins/calendars/interactsWithSelectedPeriods.js';

import EVENTS from '@/graphql/calendar/queries/Events.gql';
import TODOS from '@/graphql/todos/queries/Todos.gql';
import EXTERNAL_EVENTS from '@/graphql/calendar/queries/ExternalEvents.gql';

// import { initializeEvents } from '@/core/repositories/eventRepository.js';
// import { initializeTodos } from '@/core/repositories/todoRepository.js';
import TodoList from '@/core/models/TodoList.js';
import IntegratableList from '@/core/models/IntegratableList.js';

export default {
    name: 'EventsCalendar',
    components: {
        CalendarMonthly,
        CalendarWeekly,
        CalendarDaily,
        TimeWithPicker,
        HylarkSimplified,
    },
    mixins: [
        interactsWithSelectedPeriods,
    ],
    props: {
        events: {
            type: [Array, null],
            required: true,
        },
        externalEvents: {
            type: [Object, null],
            required: true,
        },
        dateRange: {
            type: [Object, null],
            required: true,
        },
        teleportRef: {
            type: [Object, null],
            default: null,
        },
        displayedList: {
            type: [Object, null],
            required: true,
        },
        hasActiveFilters: Boolean,
    },
    emits: [
        'selectEvent',
        'update:dateRange',
    ],
    // Leaving this here due to todos code
    // apollo: {
    //     events: {
    //         query() {
    //             return this.generateQuery();
    //         },
    //         update(response) {
    //             if (this.displayedList instanceof TodoList) {
    //                 return initializeTodos(response);
    //             }
    //             return initializeEvents(response);
    //         },
    //         variables() {
    //             return this.generateVariables();
    //         },
    //     },
    // },
    data() {
        return {
            currentTime: utils.formattedTime(),
            currentDate: utils.dateWithTz(),
            newDay: null,
        };
    },
    computed: {
        rangeDiff() {
            if (this.dateRange) {
                return this.dateRange[1].diff(this.dateRange[0]);
            }
            return 0;
        },
        viewedFullObject: {
            get() {
                if (this.dateRange) {
                    const range = this.dateRange;
                    return range[0].add(this.rangeDiff / 2);
                }
                return this.$dayjs();
            },
            set(date) {
                const dateRange = [
                    date.subtract(30, 'day'),
                    date.add(30, 'day'),
                ];
                this.$emit('update:dateRange', dateRange);
            },
        },
        allEventsLength() {
            return this.allEvents.length || 0;
        },
        allEvents() {
            if (this.hasExternalFunctionality) {
                return this.externalEventsArr;
            }
            return this.events;
        },
        externalEventsArr() {
            return this.externalEvents?.data || [];
        },
        hasExternalFunctionality() {
            return this.displayedList?.isExternalList() && !this.hasActiveFilters;
        },
        calendarComponent() {
            return `Calendar${_.pascalCase(this.selectedPeriod)}`;
        },
        currentPeriodDisplay() {
            if (this.selectedPeriod === 'DAILY') {
                return 'DAY_MONTH_YEAR';
            }
            if (this.selectedPeriod === 'WEEKLY') {
                return 'WEEK_RANGE';
            }
            return 'MONTH_YEAR';
        },
        isNotToday() {
            if (this.currentYear !== this.viewedYear
                || this.currentMonth !== this.viewedMonth) {
                return true;
            }
            if (this.selectedPeriod === 'DAILY') {
                return this.currentDay !== this.viewedDay;
            }
            return !this.currentDate.isBetween(this.firstWeekday, this.lastWeekday);
        },
        currentYear() {
            return this.currentDate?.year();
        },
        currentMonth() {
            return this.currentDate?.month();
        },
        currentDay() {
            return this.currentDate?.date();
        },
        todayFormatted() {
            return this.currentDate?.format('ddd ll');
        },
    },
    methods: {
        selectEvent(event) {
            this.$emit('selectEvent', event);
        },
        selectedClasses(period) {
            return this.isSelectedPeriod(period) ? 'text-cm-00 bg-primary-600' : 'text-primary-600';
        },
        goToToday() {
            this.setViewedDate(this.$dayjs());
        },
        setViewedDate(date) {
            this.viewedFullObject = this.$dayjs(date);
        },
        // Leaving this here due to todos code
        generateQuery() {
            if (
                this.activeFilters.filter !== 'all'
                && this.displayedList instanceof IntegratableList
                && this.displayedList.isExternalList()
            ) {
                return EXTERNAL_EVENTS;
            }
            if (this.displayedList instanceof TodoList) {
                return TODOS;
            }
            return EVENTS;
        },
        generateVariables() {
            const isTodo = this.displayedList instanceof TodoList;
            const variables = {
                includeRecurringInstances: true,
                [isTodo ? 'dueAfter' : 'endsAfter']: this.viewedFullObject.subtract(1, 'month'),
                [isTodo ? 'dueBefore' : 'startsBefore']: this.viewedFullObject.add(1, 'month'),
            };

            if (this.page?.mapping) {
                variables.forMapping = this.page.mapping.id;
            }

            if (this.activeFilters.filter !== 'all') {
                if (this.displayedList) {
                    variables[isTodo ? 'listId' : 'calendarId'] = this.displayedList.id;
                    if (this.displayedList.isExternalList()) {
                        variables.sourceId = this.displayedList.account.id;
                    }
                }
            }

            return variables;
        },
    },
    created() {
        this.interval = window.setInterval(() => {
            this.currentTime = utils.formattedTime();
            this.currentDate = utils.dateWithTz();
        }, 1000);
        if (!this.dateRange) {
            this.viewedFullObject = this.$dayjs();
        }
    },
    unmounted() {
        window.clearInterval(this.interval);
    },
};
</script>

<style scoped>

.o-events-calendar {
    &__panel {
        @apply
            bg-cm-00
        ;
    }

    &__periods {
        @apply
            bg-primary-100
            flex
            font-semibold
            p-1
            rounded-lg
            text-xssm
        ;
    }

    &__period {
        @apply
            px-2.5
            py-1.5
            rounded-md
        ;
    }

    &__switch {
        height:  25px;
        transition: 0.3s ease-in-out;
        width: 25px;

        @apply
            border
            border-cm-300
            border-solid
            rounded-md
            text-primary-600
        ;

        &:hover {
            @apply
                bg-primary-100
                border-primary-600
            ;
        }
    }
}

</style>

<style>
.o-events-calendar {
    &__days {
        @apply
            bg-primary-100
            flex
            py-3
            rounded-t-xl
            text-sm
            w-full
        ;
    }

    &__weekday {
        @apply
            text-center
            text-primary-600
        ;
    }

    &__seventh {
        width: 14.28%;
    }
}
</style>
