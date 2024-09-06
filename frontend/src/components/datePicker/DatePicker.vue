<template>
    <div
        class="c-date-picker"
        :style="{ width: pickerWidth }"
    >
        <CalendarPicker
            v-if="showDay"
            :dateTime="dateTime"
            :timeOptionsProp="timeOptionsProp"
            :dateNullable="dateNullable"
            v-bind="$attrs"
            @update:dateTime="emitDate($event)"
        >
        </CalendarPicker>

        <div
            v-if="showTime"
            class="mt-4 flex justify-center"
        >
            <TimeInput
                :dateTime="dateTime"
                :timeOptionsProp="timeOptionsProp"
                :showClear="showTimeClear"
                v-bind="$attrs"
                @update:dateTime="emitDate($event)"
            >
            </TimeInput>
        </div>
    </div>
</template>

<script>

import CalendarPicker from '@/components/datePicker/CalendarPicker.vue';
import TimeInput from '@/components/dateTimeInputs/TimeInput.vue';

export default {
    name: 'DatePicker',
    components: {
        CalendarPicker,
        TimeInput,
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
        mode: {
            type: String,
            default: 'DATE',
            validator(val) {
                return ['DATE', 'DATE_TIME', 'TIME'].includes(val);
            },
        },
        width: {
            type: Number,
            default: 280,
        },
        displayOnly: Boolean,
        dateNullable: Boolean,
        showTimeClear: Boolean,
    },
    emits: [
        'update:dateTime',
    ],
    computed: {
        showDay() {
            return this.mode === 'DATE' || this.mode === 'DATE_TIME';
        },
        showTime() {
            return this.mode === 'TIME' || this.mode === 'DATE_TIME';
        },
        pickerWidth() {
            return `${this.width}px`;
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

/*.c-date-picker {

}*/

</style>
