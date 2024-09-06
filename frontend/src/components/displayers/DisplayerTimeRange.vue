<template>
    <div
        v-if="displayFieldValue"
        class="c-displayer-time-range text-xssm inline-flex flex-col"
    >
        <div
            :class="displayClasses"
        >
            <i
                class="fal fa-clock mr-1"
            >
            </i>

            <template
                v-if="toValue && fromValue"
            >
                {{ from }} - {{ to }}
            </template>
            <template
                v-if="toValue && !fromValue"
            >
                Before {{ to }}
            </template>
            <template
                v-if="!toValue && fromValue"
            >
                After {{ from }}
            </template>
        </div>
    </div>
</template>

<script>

import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';

import interactsWithTimeDisplayers from '@/vue-mixins/displayers/interactsWithTimeDisplayers.js';

export default {
    name: 'DisplayerTimeRange',
    components: {
    },
    mixins: [
        interactsWithDisplayers,
        interactsWithTimeDisplayers,
    ],
    props: {

    },
    data() {
        return {
            typeKey: 'TIME_RANGE',
        };
    },
    computed: {
        fromValue() {
            return this.displayFieldValue.from;
        },
        toValue() {
            return this.displayFieldValue.to;
        },
        from() {
            return this.formatTheTime(this.fromValue);
        },
        to() {
            return this.formatTheTime(this.toValue);
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.c-displayer-time-range {

} */

</style>
