<template>
    <div class="o-preferences-time">

        <SettingsHeaderLine
            class="mb-10"
        >
            <template
                #header
            >
                Timezone
            </template>

            <div>
                <TimezoneDropdown
                    :modelValue="timezone"
                    :error="timezoneError"
                    @update:modelValue="$emit('update:timezone', $event)"
                >
                </TimezoneDropdown>
            </div>

        </SettingsHeaderLine>

        <SettingsHeaderLine
            class="mb-10"
        >
            <template
                #header
            >
                Week start
            </template>

            <WeekdayPicker
                :weekdayStart="weekdayStart"
                @update:weekdayStart="$emit('update:weekdayStart', $event)"
            >
            </WeekdayPicker>

        </SettingsHeaderLine>

        <SettingsHeaderLine
            class="mb-10"
        >
            <template
                #header
            >
                Time format
            </template>

            <div class="flex">
                <div
                    class="mr-8"
                >
                    <CheckHolder
                        :modelValue="timeFormat"
                        val="12"
                        type="radio"
                        @update:modelValue="$emit('update:timeFormat', $event)"
                    >
                        12 hour
                    </CheckHolder>
                </div>

                <div>
                    <CheckHolder
                        :modelValue="timeFormat"
                        val="24"
                        type="radio"
                        @update:modelValue="$emit('update:timeFormat', $event)"
                    >
                        24 hour
                    </CheckHolder>
                </div>
            </div>

        </SettingsHeaderLine>

        <SettingsHeaderLine>
            <template
                #header
            >
                Default date format
            </template>

            <div class="flex flex-wrap">
                <div
                    v-for="option in dateOptions"
                    :key="option.val"
                    class="mr-8 my-1"
                >
                    <CheckHolder
                        :modelValue="dateFormat"
                        :val="option.val"
                        type="radio"
                        @update:modelValue="$emit('update:dateFormat', $event)"
                    >
                        {{ option.text }}
                    </CheckHolder>
                </div>
            </div>

        </SettingsHeaderLine>
    </div>
</template>

<script>

import WeekdayPicker from '@/components/time/WeekdayPicker.vue';
import TimezoneDropdown from '@/components/time/TimezoneDropdown.vue';

const dateOptions = [
    {
        val: 'DMY',
        text: 'dd/mm/yyyy',
    },
    {
        val: 'MDY',
        text: 'mm/dd/yyyy',
    },
    {
        val: 'YMD',
        text: 'yyyy/mm/dd',
    },
];

export default {
    name: 'PreferencesTime',
    components: {
        WeekdayPicker,
        TimezoneDropdown,
    },
    mixins: [
    ],
    props: {
        weekdayStart: {
            type: Number,
            default: 1,
        },
        timezone: {
            type: String,
            default: '',
        },
        dateFormat: {
            type: String,
            default: 'DMY',
        },
        timeFormat: {
            type: String,
            default: '12',
        },
        timezoneError: {
            type: String,
            default: '',
        },
    },
    emits: [
        'update:weekdayStart',
        'update:timeFormat',
        'update:dateFormat',
        'update:timezone',
    ],
    data() {
        return {
        };
    },
    computed: {
    },
    methods: {

    },
    created() {
        this.dateOptions = dateOptions;
    },
};
</script>

<!-- <style scoped>
.o-preferences-time {

}
</style> -->
