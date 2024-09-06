<template>
    <div
        v-if="displayFieldValue"
        class="c-displayer-date-time inline-flex flex-col text-xssm"
    >
        <div
            :class="displayClasses"
        >
            <i
                v-if="showIcon"
                class="fal fa-calendar mr-1 text-cm-400"
            >
            </i>

            {{ dateTime }}
        </div>
    </div>
</template>

<script>

import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';

export default {
    name: 'DisplayerDateTime',
    components: {

    },
    mixins: [
        interactsWithDisplayers,
    ],
    props: {

    },
    data() {
        return {
            typeKey: 'DATE_TIME',
        };
    },
    computed: {
        longFormat() {
            return 'll';
        },
        preferenceFormat() {
            return utils.dateDayjsFormat();
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
        inUtc() {
            return this.$dayjs.tz(this.displayFieldValue, 'utc');
        },
        dateTime() {
            return this.$dayjs(this.inUtc).format(`${this.dateFormat} ${this.timeFormat}`);
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.c-displayer-date-time {

} */

</style>
