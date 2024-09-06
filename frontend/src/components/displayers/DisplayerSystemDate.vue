<template>
    <div
        class="c-displayer-system-date text-xssm"
        :class="displayClasses"
    >
        {{ $dayjs(dataValue).format('lll') }}
    </div>
</template>

<script>

import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';

export default {
    name: 'DisplayerSystemDate',
    components: {

    },
    mixins: [
        interactsWithDisplayers,
    ],
    props: {

    },
    data() {
        return {
            typeKey: 'SYSTEM_DATE',
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

/*.c-displayer-system-date {

} */

</style>
