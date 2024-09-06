<template>
    <div
        v-if="displayFieldValue"
        class="c-displayer-date-time-range inline-flex flex-col text-xssm"
    >
        <div
            class="flex items-baseline"
            :class="displayClasses"
        >
            <i
                v-if="showIcon"
                class="fal fa-calendar mr-2 text-cm-400"
            >
            </i>

            <div>
                <div class="flex flex-wrap">
                    <div
                        v-if="fromValue"
                    >
                        <template
                            v-if="!toValue"
                        >
                            After
                        </template>
                        {{ from }}
                    </div>
                    <span
                        v-if="fromValue && toValue"
                        class="mx-1"
                    >
                        -
                    </span>
                </div>
                <div
                    v-if="toValue"
                >
                    <template
                        v-if="!fromValue"
                    >
                        Before
                    </template>
                    {{ to }}
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';

export default {
    name: 'DisplayerDateTimeRange',
    components: {

    },
    mixins: [
        interactsWithDisplayers,
    ],
    props: {

    },
    data() {
        return {
            typeKey: 'DATE_TIME_RANGE',
        };
    },
    computed: {
        longFormat() {
            return 'll';
        },
        preferenceFormat() {
            return utils.dateInFormat();
        },
        fromValue() {
            return this.displayFieldValue?.from;
        },
        toValue() {
            return this.displayFieldValue?.to;
        },
        fromObj() {
            return this.$dayjs(this.fromValue);
        },
        toObj() {
            return this.$dayjs(this.toValue);
        },
        fromDate() {
            return this[this.comboFormat](this.fromObj);
        },
        toDate() {
            return this[this.comboFormat](this.toObj);
        },

        comboFormat() {
            return this.selectedCombo.format;
        },
        showIcon() {
            return this.selectedCombo.showIcon;
        },
        dateFormat() {
            return this[this.comboFormat];
        },
        timeFormat() {
            return utils.timeDayjsFormat();
        },
        fromInUtc() {
            return this.$dayjs.tz(this.fromValue, 'utc');
        },
        toInUtc() {
            return this.$dayjs.tz(this.toValue, 'utc');
        },
        to() {
            return this.toInUtc.format(`${this.dateFormat} ${this.timeFormat}`);
        },
        from() {
            return this.fromInUtc.format(`${this.dateFormat} ${this.timeFormat}`);
        },
    },
    methods: {
    },
    created() {

    },
};
</script>

<style scoped>

/*.c-displayer-date-time-range {

} */

</style>
