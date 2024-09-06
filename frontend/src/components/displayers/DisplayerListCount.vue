<template>
    <div
        class="c-displayer-list-count inline-block"
    >
        <div
            :class="displayClasses"
        >
            {{ count }}
        </div>
    </div>
</template>

<script>

import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';

export default {
    name: 'DisplayerListCount',
    components: {

    },
    mixins: [
        interactsWithDisplayers,
    ],
    props: {
    },
    data() {
        return {
            typeKey: 'LIST_COUNT',
        };
    },
    computed: {
        count() {
            return this.dataValue || 0;
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.c-displayer-list-count {

} */

</style>
