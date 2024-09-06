<template>
    <div
        v-if="displayFieldValue"
        class="c-displayer-phone text-sm"
    >
        <a
            class="c-displayer-phone__phone"
            :class="displayClasses"
            :href="'tel:' + displayFieldValue"
            @click.stop
        >
            {{ displayFieldValue }}
        </a>
    </div>
</template>

<script>

import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';

export default {
    name: 'DisplayerPhone',
    components: {

    },
    mixins: [
        interactsWithDisplayers,
    ],
    props: {

    },
    data() {
        return {
            typeKey: 'PHONE',
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

.c-displayer-phone {
    &__phone:hover {
        @apply
            text-sky-500
        ;
    }
}

</style>
