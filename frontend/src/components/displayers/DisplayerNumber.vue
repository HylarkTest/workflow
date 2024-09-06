<template>
    <div
        v-if="displayFieldValue"
        class="c-displayer-number inline-block"
    >
        <div
            :class="displayClasses"
        >
            {{ displayFieldValue }}
        </div>
    </div>
</template>

<script>

import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';

export default {
    name: 'DisplayerNumber',
    components: {

    },
    mixins: [
        interactsWithDisplayers,
    ],
    props: {
    },
    data() {
        return {
            typeKey: 'NUMBER',
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

/*.c-displayer-number {

} */

</style>
