<template>
    <div
        v-if="displayFieldValue"
        class="c-displayer-date text-xssm inline-flex flex-col"
    >
        <div
            :class="displayClasses"
        >
            <i
                v-if="showIcon"
                class="fal fa-calendar mr-1 text-cm-400"
            >
            </i>

            {{ date }}
        </div>
    </div>
</template>

<script>

import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';

export default {
    name: 'DisplayerDate',
    components: {

    },
    mixins: [
        interactsWithDisplayers,
    ],
    props: {

    },
    data() {
        return {
            typeKey: 'DATE',
        };
    },
    computed: {
        dateObj() {
            return this.$dayjs(this.displayFieldValue);
        },
        longFormat() {
            return this.dateObj.format('ll');
        },
        preferenceFormat() {
            return utils.dateInFormat(this.dateObj);
        },
        comboFormat() {
            return this.selectedCombo.format;
        },
        showIcon() {
            return this.selectedCombo.showIcon;
        },
        date() {
            return this[this.comboFormat];
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.c-displayer-date {

} */

</style>
