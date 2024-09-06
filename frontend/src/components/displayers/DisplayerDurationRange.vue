<template>
    <div
        v-if="displayFieldValue"
        class="c-displayer-duration-range flex flex-wrap -m-1 items-center text-xssm"
        :class="displayClasses"
    >
        <span
            v-if="!to"
            class="block mr-0.5"
        >
            Over
        </span>
        <span
            v-if="!from"
            class="block mr-0.5"
        >
            Under
        </span>
        <div
            v-for="field in fromFields"
            :key="field.id"
            class="flex m-1"
        >
            <span class="mr-0.5">
                {{ field.value }}
            </span>
            <span>
                {{ getSuffix(field.id, field.value) }}
            </span>
        </div>
        <span
            v-if="from && to"
            class="block mx-0.5"
        >
            -
        </span>
        <div
            v-for="field in toFields"
            :key="field.id"
            class="flex m-1"
        >
            <span class="mr-0.5">
                {{ field.value }}
            </span>
            <span>
                {{ getSuffix(field.id, field.value) }}
            </span>
        </div>
    </div>
</template>

<script>

import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';

const dateFormats = {
    MONTHS: 'monthly',
    WEEKS: 'weekly',
    DAYS: 'daily',
    HOURS: 'hourly',
    MINUTES: 'minutely',
};

export default {
    name: 'DisplayerDurationRange',
    components: {

    },
    mixins: [
        interactsWithDisplayers,
    ],
    props: {

    },
    data() {
        return {
            typeKey: 'DURATION_RANGE',
            lines: [
                'MONTHS',
                'WEEKS',
                'DAYS',
                'HOURS',
                'MINUTES',
            ],
        };
    },
    computed: {
        from() {
            return this.displayFieldValue?.from;
        },
        to() {
            return this.displayFieldValue?.to;
        },
        fromFields() {
            return this.getFields(this.from);
        },
        toFields() {
            return this.getFields(this.to);
        },
    },
    methods: {
        getSuffix(val, fieldValue) {
            const valKey = dateFormats[val];
            return this.$tc(`common.dates.suffixes.${valKey}`, fieldValue);
        },
        getFields(fromMax) {
            const lines = this.getLines(fromMax);
            return lines.filter((line) => {
                return _.isNumber(line.value);
            });
        },
        getLines(fromMax) {
            return this.lines.map((line) => {
                const formatted = _.camelCase(line);
                return {
                    id: line,
                    value: fromMax?.[formatted],
                };
            });
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.c-displayer-duration-range {

} */

</style>
