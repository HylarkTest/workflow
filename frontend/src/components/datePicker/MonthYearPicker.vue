<template>
    <div class="c-month-year-picker">

        <div class="flex justify-center mb-2">
            <YearViewedOptions
                :viewedYear="viewedYear"
                @selectYear="$emit('selectYear', $event)"
            >
            </YearViewedOptions>
        </div>

        <MonthOptions
            v-bind="$attrs"
        >
        </MonthOptions>
    </div>
</template>

<script>

import MonthOptions from './MonthOptions.vue';
import YearViewedOptions from './YearViewedOptions.vue';

export default {
    name: 'MonthYearPicker',
    components: {
        MonthOptions,
        YearViewedOptions,
    },
    mixins: [
    ],
    props: {
        viewedYear: {
            type: [Number, String],
            required: true,
        },
    },
    emits: [
        'selectYear',
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

    },
};
</script>

<style scoped>

/*.c-month-year-picker {

} */

</style>
