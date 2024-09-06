<template>
    <div
        class="c-displayer-name u-text"
        :class="displayClasses"
    >
        {{ displayFieldValue }}
    </div>
</template>

<script>

import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';

export default {
    name: 'DisplayerName',
    components: {

    },
    mixins: [
        interactsWithDisplayers,
    ],
    props: {

    },
    data() {
        return {
            typeKey: 'NAME',
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

/*.c-displayer-name {

} */

</style>
