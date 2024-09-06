<template>
    <div class="o-monthly-cell">
        <ButtonEl
            class="o-monthly-cell__date relative"
            @click.stop="openDaily"
        >
            <div
                class="centered"
                :class="isToday ? 'w-6 mr-0.5' : 'o-monthly-cell__day transition-2eio'"
            >
                <div
                    class="text-right leading-none font-semibold text-sm relative z-over"
                    :class="dateColor"
                >
                    {{ day.date() }}
                </div>
            </div>
            <HylarkSimplified
                v-if="isToday"
                class="h-6 w-6 absolute -top-2 z-0"
                :hasHoverEffect="true"
            ></HylarkSimplified>
        </ButtonEl>

        <div class="text-xs relative">
            <div
                v-for="visual in firstEvents"
                :key="visual.event.id"
                class="absolute w-full"
                :style="{ top: `${visual.column * 24}px` }"
            >
                <MonthlyEvent
                    class="o-monthly-cell__event"
                    :event="visual.event"
                    :cellDateObj="day"
                    :weekStart="weekStart"
                    :dayIndex="dayIndex"
                    @selectEvent="selectEvent"
                >
                </MonthlyEvent>
            </div>
        </div>

        <ButtonEl
            v-if="hasRemaining"
            class="o-monthly-cell__more"
            style="top: 72px;"
            @click.stop="openDaily"
        >
            <span
                class="text-center hover:bg-cm-100 hover:text-primary-600 rounded-full px-1 py-0.5"
            >
                {{ remainingLength }} more
            </span>
        </ButtonEl>

        <DailyModal
            v-if="isModalOpen"
            :events="eventsArr"
            :viewedFullObject="day"
            @closeModal="closeModal"
            @selectEvent="selectEvent"
            @newEvent="newEvent"
        >
        </DailyModal>
    </div>
</template>

<script>

import DailyModal from './DailyModal.vue';
import MonthlyEvent from './MonthlyEvent.vue';
import HylarkSimplified from '@/components/branding/HylarkSimplified.vue';

import interactsWithMonthlyCell from '@/vue-mixins/calendars/interactsWithMonthlyCell.js';
import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

export default {
    name: 'MonthlyCell',
    components: {
        HylarkSimplified,
        MonthlyEvent,
        DailyModal,
    },
    mixins: [
        interactsWithMonthlyCell,
        interactsWithModal,
    ],
    props: {
        daysEvents: {
            type: Array,
            default: () => ([]),
        },
        weekStart: {
            type: Number,
            required: true,
        },
        dayIndex: {
            type: Number,
            required: true,
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
        firstEvents() {
            return this.daysEvents.filter(({ column }) => column < 3);
        },
        hasRemaining() {
            return this.remainingLength > 0;
        },
        remainingLength() {
            return this.daysEvents.filter(({ column }) => column >= 3).length;
        },
        eventsArr() {
            return this.daysEvents.map(({ event }) => event);
        },
    },
    methods: {
        openDaily() {
            this.openModal();
        },
        selectEvent(event) {
            this.$emit('selectEvent', event);
        },
        newEvent(date) {
            this.$emit('newEvent', date);
        },
    },
    created() {

    },
};
</script>

<style scoped>

.o-monthly-cell {
    &__date {
        margin: 6px 6px 3px 6px;

        @apply
            flex
            justify-end
        ;

        &:hover {
            & :deep(.c-hylark-simplified__beak) {
                @apply
                    bg-gold-400
                ;
            }

            & :deep(.c-hylark-simplified__body) {
                @apply
                    bg-azure-400
                ;
            }
        }
    }

    &__day {
        @apply
            h-6
            hover:bg-cm-100
            rounded-full
            w-6
        ;
    }

    &__more {
        @apply
            inline-flex
            justify-center
            mt-0.5
            relative
            text-cm-500
            text-xs
            w-full
        ;
    }
}

</style>
