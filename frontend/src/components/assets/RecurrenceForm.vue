<template>
    <div class="o-recurrence-form relative flex">
        <div
            v-if="hasRecurrenceSaved"
            class="flex justify-between items-center"
        >
            <RecurrenceDisplay
                :recurrence="recurrence"
                :longVersion="true"
            >
            </RecurrenceDisplay>

            <ClearButton
                positioningClass="relative ml-2"
                @click="clearRecurrence"
            >
            </ClearButton>
        </div>
        <div v-else>
            <div class="flex items-baseline justify-between mb-2">
                <label
                    v-t="'labels.frequency'"
                    class="mr-2 text-cm-400 medium"
                >
                </label>
                <DropdownBox
                    :modelValue="frequency"
                    class="w-32"
                    labelType="top"
                    :options="periodOptions"
                    :displayRule="periodOptionsDisplay"
                    :showClear="true"
                    placeholder="Daily, weekly, etc..."
                    bgColor="gray"
                    @update:modelValue="changeRecurrence($event, 'frequency')"
                >
                </DropdownBox>
            </div>

            <div
                v-if="frequency"
                class="flex items-baseline justify-between"
            >
                <label
                    v-t="'labels.every'"
                    class="mr-2 text-cm-400 font-medium"
                >
                </label>

                <div class="flex items-baseline">
                    <InputBox
                        v-blur="adjustInterval"
                        :modelValue="interval"
                        type="number"
                        min="1"
                        max="100"
                        labelType="top"
                        bgColor="gray"
                        @update:modelValue="updateInterval"
                    >
                    </InputBox>

                    <p
                        class="ml-1"
                    >
                        {{ $tc(suffixString, interval) }}
                    </p>
                </div>
            </div>

            <div
                v-if="frequency === 'WEEKLY'"
                class="flex items-baseline justify-between mt-2"
            >
                <label
                    v-t="'labels.on'"
                    class="mr-2 text-cm-400 font-medium"
                >
                </label>
                <div
                    class="o-recurrence-form__weekdays"
                >
                    <button
                        v-for="(weekday, index) in weekdays"
                        :key="weekday"
                        class="o-recurrence-form__weekday centered"
                        :class="[{ 'bg-primary-600 text-cm-00': isSelectedDay(weekday) }, roundedClass(index)]"
                        type="button"
                        @click="selectWeekday(weekday)"
                    >
                        {{ $t(`common.dates.days.${weekday}.one`) }}
                    </button>
                </div>
            </div>

            <div
                v-if="hasSaveButton"
                class="flex justify-end mt-2"
            >
                <button
                    type="button"
                    class="button--sm bg-primary-600 text-cm-00"
                    :class="{ 'opacity-50 pointer-events-none': !showAdd }"
                    :disabled="!showAdd"
                    @click="saveRepeat"
                >
                    Add repeat
                </button>
            </div>
        </div>
        <transition name="t-fade">
            <AlertTooltip
                v-if="error"
                :alertPosition="{ bottom: '110px', right: 0 }"
            >
                {{ error }}
            </AlertTooltip>
        </transition>
    </div>
</template>

<script>

import { arrRemove } from '@/core/utils.js';
import { repeatPeriods } from '@/core/data/repeatOptions.js';

import ClearButton from '@/components/buttons/ClearButton.vue';
import RecurrenceDisplay from '@/components/time/RecurrenceDisplay.vue';

import interactsWithWeekdays from '@/vue-mixins/calendars/interactsWithWeekdays.js';
import AlertTooltip from '@/components/popups/AlertTooltip.vue';

export default {
    name: 'RecurrenceForm',
    components: {
        AlertTooltip,
        ClearButton,
        RecurrenceDisplay,
    },
    mixins: [
        interactsWithWeekdays,
    ],
    props: {
        recurrence: {
            type: [Object, null],
            default: null,
        },
        originalRecurrence: {
            type: Object,
            default: () => ({}),
        },
        hasSaveButton: Boolean,
        error: {
            type: String,
            default: '',
        },
    },
    emits: [
        'saveRepeat',
        'update:recurrence',
    ],
    data() {
        return {
            recurrenceChanged: false,
        };
    },
    computed: {
        frequency() {
            return this.recurrence?.frequency;
        },
        showAdd() {
            return this.interval && this.frequency;
        },
        interval() {
            return this.recurrence?.interval;
        },
        hasRecurrenceSaved() {
            return this.originalRecurrence && this.recurrence && !this.recurrenceChanged;
        },
        selectedWeekdays() {
            return this.recurrence?.byDay || [];
        },
        suffixString() {
            return `common.dates.suffixes.${_.camelCase(this.frequency)}`;
        },
    },
    methods: {
        selectWeekday(day) {
            let days;
            if (this.isSelectedDay(day)) {
                days = arrRemove(this.selectedWeekdays, day);
            } else {
                days = [...this.selectedWeekdays, day];
            }
            this.changeRecurrence(days, 'byDay');
        },
        isSelectedDay(day) {
            return this.selectedWeekdays.includes(day);
        },
        roundedClass(index) {
            if (index === 0) {
                return 'rounded-l-md';
            }
            if (index === 6) {
                return 'rounded-r-md';
            }
            return '';
        },
        clearRecurrence() {
            this.$emit('update:recurrence', null);
            this.saveRepeat();
        },
        saveRepeat() {
            this.$emit('saveRepeat');
        },
        updateInterval(val) {
            const newVal = _.isFinite(val) ? val : 1;
            this.changeRecurrence(newVal, 'interval');
        },
        adjustInterval() {
            const isOutsideBounds = this.recurrence.interval < 1 || this.recurrence.interval > 100;
            if (isOutsideBounds) {
                const newVal = this.recurrence.interval < 1 ? 1 : 100;
                this.changeRecurrence(newVal, 'interval');
            }
        },
        changeRecurrence(val, key) {
            if (val === null && key === 'frequency') {
                // Reset if frequency dropdown value is removed
                this.$emit('update:recurrence', null);
            } else {
                let newRecurrence = {};

                if (this.recurrence) {
                    newRecurrence = _.clone(this.recurrence);
                    newRecurrence[key] = val;
                } else {
                    _.set(newRecurrence, key, val);
                }
                this.$emit('update:recurrence', newRecurrence);
            }
        },
    },
    watch: {
        recurrence() {
            this.recurrenceChanged = true;
        },
    },
    created() {
        this.periodOptions = repeatPeriods;
        this.periodOptionsDisplay = (option) => this.$t(`common.dates.${_.toLower(option)}`);
    },
};
</script>

<style scoped>

.o-recurrence-form {
    @apply
        text-sm
    ;

    &__weekdays {
        @apply
            border
            border-cm-300
            border-solid
            inline-flex
            rounded-md
        ;
    }

    &__weekday {
        @apply
            h-8
            w-7
        ;
    }
}

</style>
