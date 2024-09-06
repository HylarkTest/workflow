<template>
    <div class="o-event-date-picker relative">
        <div class="mb-2 inline-flex">
            <CheckHolder
                :modelValue="isAllDay"
                size="sm"
                name="isAllDay"
                @update:modelValue="$emit('update:isAllDay', $event)"
            >
                All day
            </CheckHolder>
        </div>

        <div
            v-for="(date, index) in dateRange"
            :key="index"
            class="flex sm:items-center mb-2 flex-col sm:flex-row"
        >
            <div class="flex sm:items-center mb-2 flex-col sm:flex-row">
                <h3 class="o-event-date-picker__title">
                    {{ date.label }}
                </h3>

                <DateTimeInput
                    :dateTime="date.value"
                    :mode="mode"
                    :timeOptionsProp="timeOptions"
                    @update:dateTime="$emit(`update:${date.propName}`, $event)"
                >
                </DateTimeInput>
            </div>
        </div>

        <transition name="t-fade">
            <AlertTooltip
                v-if="error"
                :alertPosition="{ bottom: '70px', right: 0 }"
            >
                {{ error }}
            </AlertTooltip>
        </transition>
    </div>
</template>

<script>
import AlertTooltip from '@/components/popups/AlertTooltip.vue';
import DateTimeInput from '@/components/dateTimeInputs/DateTimeInput.vue';

export default {
    name: 'EventDatePicker',
    components: {
        AlertTooltip,
        DateTimeInput,
    },
    mixins: [
    ],
    props: {
        isAllDay: Boolean,
        startDate: { // UTC, any format
            type: [String, Object, null],
            required: true,
        },
        endDate: { // UTC, any format
            type: [String, Object, null],
            required: true,
        },
        timezone: {
            type: String,
            required: true,
        },
        error: {
            type: String,
            default: '',
        },
    },
    emits: [
        'update:isAllDay',
        'update:startDate',
        'update:endDate',
    ],
    data() {
        return {
        };
    },
    computed: {
        dateRange() {
            return [
                {
                    value: this.startDate,
                    propName: 'startDate',
                    label: 'From',
                },
                {
                    value: this.endDate,
                    propName: 'endDate',
                    label: 'To',
                },
            ];
        },
        mode() {
            return this.isAllDay ? 'DATE' : 'DATE_TIME';
        },
        timeOptions() {
            return {
                forceDate: true,
                forceTime: true,
                forceAllDay: this.isAllDay,
            };
        },
    },
    methods: {
    },
    created() {
    },
};
</script>

<style scoped>

.o-event-date-picker {
    &__title {
        min-width: 50px;

        @apply
            font-medium
            mr-2
            text-cm-400
        ;
    }
}

</style>
