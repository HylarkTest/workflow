<template>
    <div
        v-if="displayFieldValue"
        class="c-displayer-url text-sm"
    >
        <a
            class="break-all"
            :class="displayClasses"
            :href="displayFieldValue"
            :title="displayFieldValue"
            target="_blank"
            rel="noopener noreferrer"
            @click.stop
        >
            <template
                v-if="combo === 2"
            >
                <i
                    class="far fa-up-right-from-square text-xs"
                >
                </i>
            </template>

            <template
                v-else
            >
                {{ displayFieldValue }}
            </template>
        </a>
    </div>
</template>

<script>

import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';

export default {
    name: 'DisplayerUrl',
    components: {

    },
    mixins: [
        interactsWithDisplayers,
    ],
    props: {

    },
    data() {
        return {
            typeKey: 'URL',
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

/*.c-displayer-url {

} */

</style>
