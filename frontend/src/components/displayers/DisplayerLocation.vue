<template>
    <div
        class="c-displayer-location"
        :class="displayClasses"
    >
        <div
            class="list-value-parent"
        >
            <ValueBasic
                v-for="val in dataValueArr"
                :key="val.id"
                :hideRemove="true"
            >
                {{ val.name }}
            </ValueBasic>
        </div>
    </div>
</template>

<script>

import interactsWithMultiDisplayers from '@/vue-mixins/displayers/interactsWithMultiDisplayers.js';
import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';

export default {
    name: 'DisplayerLocation',
    components: {

    },
    mixins: [
        interactsWithDisplayers,
        interactsWithMultiDisplayers,
    ],
    props: {

    },
    data() {
        return {
            typeKey: 'LOCATION',
        };
    },
    computed: {
        fieldValue() {
            return this.displayFieldValue;
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.c-displayer-location {

} */

</style>
