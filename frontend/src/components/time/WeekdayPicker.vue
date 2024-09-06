<template>
    <div class="c-weekday-picker">
        <ButtonEl
            v-for="day in weekdays"
            :key="day"
            class="c-weekday-picker__button circle-center"
            :class="selectedClasses(day)"
            type="button"
            @click="setWeekday(day)"
        >
            <div
                v-show="!isSelected(day)"
                class="c-weekday-picker__circle"
            >

            </div>

            <div
                class="relative z-over"
            >
                {{ $t(`common.dates.days.${day}.short`) }}
            </div>
        </ButtonEl>
    </div>
</template>

<script>

const weekdays = [
    1, 2, 3, 4, 5, 6, 0,
];

export default {
    name: 'WeekdayPicker',
    components: {

    },
    mixins: [
    ],
    props: {
        weekdayStart: {
            type: Number,
            required: true,
        },
    },
    emits: [
        'update:weekdayStart',
    ],
    data() {
        return {

        };
    },
    computed: {

    },
    methods: {
        setWeekday(day) {
            this.$emit('update:weekdayStart', day);
        },
        isSelected(day) {
            return day === this.weekdayStart;
        },
        selectedClasses(day) {
            return this.isSelected(day) ? 'c-weekday-picker__button--selected' : 'text-cm-600';
        },
    },
    created() {
        this.weekdays = weekdays;
    },
};
</script>

<style scoped>

.c-weekday-picker {
    @apply
        flex
        -mx-1
    ;

    &__button {
        transition: 0.2s ease-in-out;

        @apply
            h-10
            mx-1
            relative
            text-sm
            w-10
        ;

        &:hover {
            .c-weekday-picker__circle {
                transform: scale(1);
            }
        }

        &--selected {
            @apply
                bg-primary-600
                font-semibold
                shadow-center-dark
                text-cm-00
            ;
        }
    }

    &__circle {
        transform: scale(0);
        transition: 0.3s ease-in-out;

        @apply
            absolute
            bg-primary-100
            h-10
            right-0
            rounded-full
            top-0
            w-10
            z-0
        ;
    }
}

</style>
