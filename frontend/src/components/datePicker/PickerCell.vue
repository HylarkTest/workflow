<template>
    <component
        :is="displayOnly ? 'div' : 'ButtonEl'"
        class="c-picker-cell centered"
        @click="selectDate"
    >
        <div
            class="c-picker-cell__inner centered"
            :class="[innerClass, displayClass]"
        >
            <HylarkSimplified
                v-if="showTodayBrand"
                class="c-picker-cell__brand"
                :hasHoverEffect="true"
                beakColor="bg-secondary-400"
                bodyColor="bg-primary-600"
            >
            </HylarkSimplified>

            <div
                v-if="showTodayHighlight"
                class="c-picker-cell__today bg-gold-600"
            >

            </div>

            <div
                class="c-picker-cell__date"
                :class="{ 'font-semibold': isToday }"
            >
                {{ day.date() }}
            </div>

            <div
                v-if="hasEvent"
                class="absolute h-1.5 w-1.5 rounded-full bg-secondary-600 left-2.5 -bottom-0"
            >

            </div>
        </div>
    </component>
</template>

<script>

import HylarkSimplified from '@/components/branding/HylarkSimplified.vue';

import interactsWithMonthlyCell from '@/vue-mixins/calendars/interactsWithMonthlyCell.js';

export default {
    name: 'PickerCell',
    components: {
        HylarkSimplified,
    },
    mixins: [
        interactsWithMonthlyCell,
    ],
    props: {
        selectedDate: {
            type: [Object, String, null],
            default: null,
        },
        colorName: {
            type: String,
            default: 'primary',
            validator: (color) => ['primary', 'secondary'].includes(color),
        },
        displayOnly: Boolean,
        hasEvent: Boolean,
        dateNullable: Boolean,
    },
    emits: [
        'selectDate',
    ],
    data() {
        return {

        };
    },
    computed: {
        dayFormatted() {
            return this.day?.format('YYYY-MM-DD');
        },
        isSelected() {
            return this.dayFormatted === this.selectedDate;
        },
        selectedClass() {
            return `bg-${this.colorName}-600 text-cm-00 font-semibold hover:bg-${this.colorName}-500`;
        },
        displayClass() {
            return { 'c-picker-cell__inner--display': this.displayOnly };
        },
        innerClass() {
            if (this.isSelected) {
                return this.selectedClass;
            }
            const hoverClass = this.displayOnly ? '' : 'hover:bg-cm-100';
            return [this.dateColor, hoverClass];
        },
        showTodayHighlight() {
            return this.isToday && this.isSelected;
        },
        showTodayBrand() {
            return this.isToday && !this.isSelected;
        },
    },
    methods: {
        selectDate() {
            if (!this.displayOnly) {
                let payload;
                if (this.isSelected && this.dateNullable) {
                    payload = null;
                } else {
                    payload = this.day;
                }
                this.$emit('selectDate', payload);
            }
        },
    },
    created() {

    },
};
</script>

<style scoped>

.c-picker-cell {
    padding: 1px;

    &__inner {
        height:  25px;
        transition:  0.2s ease-in-out;
        width: 25px;

        @apply
            relative
            rounded-lg
        ;

        &:not(.c-picker-cell__inner--display):hover {
            & :deep(.c-hylark-simplified__beak) {
                @apply
                    bg-secondary-500
                ;
            }

            & :deep(.c-hylark-simplified__body) {
                @apply
                    bg-primary-500
                ;
            }

            .c-picker-cell__today {
                @apply
                    bg-secondary-500
                ;
            }
        }
    }

    &__date {
        @apply
            relative
            z-over
        ;
    }

    &__brand {
        height:  25px;
        right: calc(50% - 14px);
        top: -3px;
        width: 25px;

        @apply
            absolute
        ;
    }

    &__today {
        right:  -2px;
        top:  -2px;
        transition:  background-color 0.2s ease-in-out;

        @apply
            absolute
            h-2
            rounded-full
            w-2
        ;
    }
}

</style>
