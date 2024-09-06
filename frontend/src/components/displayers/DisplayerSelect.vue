<template>
    <div
        class="c-displayer-select"
        :class="displayClasses"
    >
        {{ displayFieldValue }}
    </div>
</template>

<script>

import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';

export default {
    name: 'DisplayerSelect',
    components: {

    },
    mixins: [
        interactsWithDisplayers,
    ],
    props: {

    },
    data() {
        return {
            typeKey: 'SELECT',
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

/*.c-displayer-select {

} */

</style>
