<template>
    <div
        v-if="displayFieldValue"
        class="c-displayer-date-range text-xssm inline-flex flex-col"
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

            <div class="flex flex-wrap">
                <div
                    v-if="fromValue"
                    class="shrink-0"
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
    name: 'DisplayerDateRange',
    components: {

    },
    mixins: [
        interactsWithDisplayers,
    ],
    props: {

    },
    data() {
        return {
            typeKey: 'DATE_RANGE',
        };
    },
    computed: {
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
        from() {
            return this[this.comboFormat](this.fromObj);
        },
        to() {
            return this[this.comboFormat](this.toObj);
        },
        comboFormat() {
            return this.selectedCombo.format;
        },
        showIcon() {
            return this.selectedCombo.showIcon;
        },
    },
    methods: {
        longFormat(date) {
            return date.format('ll');
        },
        preferenceFormat(date) {
            return utils.dateInFormat(date);
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.c-displayer-date-range {

} */

</style>
