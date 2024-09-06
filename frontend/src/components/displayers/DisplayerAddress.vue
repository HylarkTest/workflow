<template>
    <div
        class="c-displayer-address text-xssm"
        :class="displayClasses"
    >
        <template v-if="hasAddress">
            <p
                v-for="(line, index) in validLines"
                :key="index"
                class="mb-0.5 last:mb-0"
            >
                {{ line }}
            </p>
        </template>
    </div>
</template>

<script>

import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';

const lines = [
    'LINE1',
    'LINE2',
    'CITY',
    'STATE',
    'COUNTRY',
    'POSTCODE',
];

export default {
    name: 'DisplayerAddress',
    components: {

    },
    mixins: [
        interactsWithDisplayers,
    ],
    props: {

    },
    data() {
        return {
            typeKey: 'ADDRESS',
        };
    },
    computed: {
        validLines() {
            return _(lines).map((line) => {
                const formatted = _.camelCase(line);
                return this.displayFieldValue?.[formatted];
            }).compact().value();
        },
        hasAddress() {
            return this.validLines?.length;
        },

    },
    methods: {

    },
    created() {
    },
};
</script>

<style scoped>

/*.c-displayer-address {

} */

</style>
