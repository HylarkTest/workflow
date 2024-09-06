<template>
    <p
        class="c-displayer-paragraph text-xssm"
        :class="displayClasses"
    >
        {{ displayFieldValue }}
    </p>
</template>

<script>

import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';

export default {
    name: 'DisplayerParagraph',
    components: {

    },
    mixins: [
        interactsWithDisplayers,
    ],
    props: {

    },
    data() {
        return {
            typeKey: 'PARAGRAPH',
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

/*.c-displayer-paragraph {

} */

</style>
