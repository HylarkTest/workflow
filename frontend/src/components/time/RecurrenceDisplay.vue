<template>
    <div
        class="c-recurrence-display flex items-center"
        :title="recurrenceFullText"
    >
        <i
            class="fal fa-arrows-repeat mr-1.5"
        >
        </i>

        <template
            v-if="shortVersion"
        >
            {{ recurrenceFormatted }}
        </template>

        <div
            v-if="longVersion"
            class="flex flex-wrap"
        >

            {{ $t('labels.every') }}

            <span class="text-primary-600 font-semibold ml-1">
                {{ recurrenceIntervalText }}
            </span>

            <div
                v-if="selectedWeekdaysLength"
            >
                <span
                    class="lowercase mx-1"
                >
                    {{ $t('labels.on') }}
                </span>

                <span
                    class="text-primary-600 font-semibold"
                >
                    {{ weekdaysJoined }}
                </span>
            </div>
        </div>
    </div>
</template>

<script>

export default {
    name: 'RecurrenceDisplay',
    components: {

    },
    mixins: [
    ],
    props: {
        recurrence: {
            type: Object,
            required: true,
        },
        longVersion: Boolean,
    },
    data() {
        return {

        };
    },
    computed: {
        recurrenceFormatted() {
            const formattedCase = _.camelCase(this.frequency);
            return this.$t(`common.dates.${formattedCase}`);
        },
        shortVersion() {
            return !this.longVersion;
        },

        interval() {
            return this.recurrence.interval;
        },
        frequency() {
            return this.recurrence.frequency;
        },
        suffixString() {
            return `common.dates.suffixes.${_.camelCase(this.frequency)}`;
        },
        selectedWeekdays() {
            return this.recurrence.byDay || [];
        },
        selectedWeekdaysLength() {
            return this.selectedWeekdays.length;
        },
        weekdayStrings() {
            return this.selectedWeekdays.map((day) => {
                return this.$t(`common.dates.days.${day}.short`);
            });
        },
        weekdayStringsLong() {
            return this.selectedWeekdays.map((day) => {
                return this.$t(`common.dates.days.${day}.full`);
            });
        },
        weekdaysJoined() {
            return this.weekdayStrings.join(', ');
        },
        weekdaysLongJoined() {
            return this.weekdayStringsLong.join(', ');
        },
        recurrenceIntervalText() {
            const intervalString = this.interval > 1 ? `${this.interval} ` : '';
            return `${intervalString}${this.$tc(this.suffixString, this.interval)}`;
        },
        recurrenceFullText() {
            const pathKey = this.selectedWeekdaysLength ? 'weeklyText' : 'text';
            return this.$t(`features.general.recurrence.${pathKey}`, {
                interval: this.recurrenceIntervalText,
                days: this.weekdaysLongJoined,
            });
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.c-recurrence-display {

} */

</style>
