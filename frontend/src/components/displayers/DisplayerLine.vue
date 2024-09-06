<template>
    <div
        class="c-displayer-line text-sm"
        :class="displayClasses"
    >
        {{ displayFieldValue }}
    </div>
</template>

<script>

import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';

export default {
    name: 'DisplayerLine',
    components: {

    },
    mixins: [
        interactsWithDisplayers,
    ],
    props: {
    },
    data() {
        return {
            typeKey: 'LINE',
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

/*.c-displayer-line {

} */

</style>
