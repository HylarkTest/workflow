<template>
    <div class="c-date-repeat-picker">
        <DatePicker
            v-bind="$attrs"
            :dateTime="dateTime"
            :timeOptionsProp="timeOptionsProp"
            :dateNullable="dateNullable"
            @update:dateTime="emitDate($event)"
        >
        </DatePicker>

        <div class="h-divider my-4">

        </div>

        <div>
            <div
                v-if="showRecurrenceTooltip"
                v-t="'todos.new.recurrenceWarning'"
                class="c-date-repeat-picker__tooltip"
            >
            </div>

            <div
                v-t="recurrenceTitlePath"
                class="text-cm-700 mb-2 font-semibold text-smbase"
            >
            </div>

            <RecurrenceForm
                v-bind="$attrs"
                :hideSubmit="true"
                :recurrence="recurrence"
            >
            </RecurrenceForm>
        </div>
    </div>
</template>

<script>

import DatePicker from '@/components/datePicker/DatePicker.vue';
import RecurrenceForm from '@/components/assets/RecurrenceForm.vue';

export default {
    name: 'DateRepeatPicker',
    components: {
        DatePicker,
        RecurrenceForm,
    },
    mixins: [
    ],
    props: {
        dateTime: {
            type: [String, Object, null],
            default: null,
        },
        timeOptionsProp: {
            type: Object,
            default: () => ({}),
        },
        recurrence: {
            type: [Object, null],
            default: null,
        },
        recurrenceTitlePath: {
            type: String,
            default: '',
        },
        hideRecurrenceTooltip: Boolean,
        dateNullable: Boolean,
    },
    emits: [
        'update:dateTime',
    ],
    data() {
        return {

        };
    },
    computed: {
        hasDate() {
            return !!this.dateTime;
        },
        hasRecurrence() {
            return !!this.recurrence;
        },
        showRecurrenceTooltip() {
            return this.hasRecurrence && !this.hasDate && !this.hideRecurrenceTooltip;
        },
    },
    methods: {
        emitDate(dateTime) {
            this.$emit('update:dateTime', dateTime);
        },

    },
    created() {

    },
};
</script>

<style scoped>

.c-date-repeat-picker {

    /* same styles as AlertTooltip */
    &__tooltip {
        @apply
            bg-sky-100
            border-none
            mb-2
            px-3
            py-1
            rounded-md
            shadow-md
            text-center
            text-sky-800
            text-sm
            z-alert
        ;
    }
}

</style>
