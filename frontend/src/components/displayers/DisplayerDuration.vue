<template>
    <div
        v-if="displayFieldValue"
        class="c-displayer-duration flex flex-wrap -m-1 text-xssm"
        :class="displayClasses"
    >
        <div
            v-for="field in fieldsFiltered"
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
    name: 'DisplayerDuration',
    components: {

    },
    mixins: [
        interactsWithDisplayers,
    ],
    props: {

    },
    data() {
        return {
            typeKey: 'DURATION',
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
        fields() {
            return this.lines.map((line) => {
                const formatted = _.camelCase(line);
                return {
                    id: line,
                    value: this.displayFieldValue[formatted],
                };
            });
        },
        fieldsFiltered() {
            return this.fields.filter((line) => {
                return _.isNumber(line.value);
            });
        },
    },
    methods: {
        getSuffix(val, fieldValue) {
            const valKey = dateFormats[val];
            return this.$tc(`common.dates.suffixes.${valKey}`, fieldValue);
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.c-displayer-duration {

} */

</style>
