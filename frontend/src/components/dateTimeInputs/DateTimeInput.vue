<template>
    <div class="c-date-time-input flex flex-wrap items-center">
        <DateInput
            v-if="showDateInput"
            class="mr-4"
            :dateTime="dateTime"
            v-bind="$attrs"
            @update:dateTime="emitDateTime($event)"
        >
        </DateInput>

        <TimeInput
            v-if="showTimeInput"
            :dateTime="dateTime"
            v-bind="$attrs"
            @update:dateTime="emitDateTime($event)"
        >
        </TimeInput>

        <ClearButton
            v-if="dateTime && showClear"
            positioningClass="ml-2"
            @click="emitDateTime(null)"
        >
        </ClearButton>
    </div>
</template>

<script>

import DateInput from '@/components/dateTimeInputs/DateInput.vue';
import TimeInput from '@/components/dateTimeInputs/TimeInput.vue';
import ClearButton from '@/components/buttons/ClearButton.vue';

export default {
    name: 'DateTimeInput',
    components: {
        DateInput,
        TimeInput,
        ClearButton,
    },
    mixins: [
    ],
    props: {
        dateTime: {
            type: [String, Object, null],
            default: null,
        },
        mode: {
            type: String,
            default: 'DATE_TIME',
            validator(val) {
                return ['DATE', 'DATE_TIME', 'TIME'].includes(val);
            },
        },
        showClear: Boolean,
    },
    emits: [
        'update:dateTime',
    ],
    computed: {
        showDateInput() {
            return this.mode === 'DATE' || this.mode === 'DATE_TIME';
        },
        showTimeInput() {
            return this.mode === 'TIME' || this.mode === 'DATE_TIME';
        },
    },
    methods: {
        emitDateTime(dateTime) {
            this.$emit('update:dateTime', dateTime);
        },
    },
};
</script>

<style scoped>

/*.c-date-time-picker {

} */

</style>
