<template>
    <div
        v-if="displayFieldValue"
        class="c-displayer-time text-xssm inline-flex flex-col"
    >
        <div
            :class="displayClasses"
        >
            <i
                class="fal fa-clock mr-1"
            >
            </i>

            {{ time }}
        </div>
    </div>
</template>

<script>

import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';

import interactsWithTimeDisplayers from '@/vue-mixins/displayers/interactsWithTimeDisplayers.js';

export default {
    name: 'DisplayerTime',
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
            typeKey: 'TIME',
        };
    },
    computed: {
        time() {
            return this.formatTheTime(this.displayFieldValue);
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.c-displayer-time {

} */

</style>
