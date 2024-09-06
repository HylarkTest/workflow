<template>
    <div
        class="c-displayer-status"
        :class="{ unclickable: processing }"
    >
        <StatusPicker
            :modelValue="dataValue"
            :group="markerGroup"
            :statusStyle="selectedCombo.style"
            @update:modelValue="editMarker"
        >
        </StatusPicker>
    </div>
</template>

<script>

import StatusPicker from '@/components/pickers/StatusPicker.vue';

import interactsWithDisplayerMarker from '@/vue-mixins/displayers/interactsWithDisplayerMarker.js';
import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';

export default {
    name: 'DisplayerStatus',
    components: {
        StatusPicker,
    },
    mixins: [
        interactsWithDisplayers,
        interactsWithDisplayerMarker,
    ],
    props: {
    },
    data() {
        return {
            typeKey: 'STATUS',
        };
    },
    computed: {
    },
    methods: {
        bgColor(color) {
            return this.$root.extraColorDisplay(color);
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.c-displayer-status {

} */

</style>
