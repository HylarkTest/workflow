<template>
    <DurationBoxes
        class="c-displayer-edit-duration"
        :duration="modifiableFieldValue"
        @update:duration="updateDataValue"
    >
    </DurationBoxes>
</template>

<script>

import interactsWithDisplayersEdit from '@/vue-mixins/displayers/interactsWithDisplayersEdit.js';

export default {
    name: 'DisplayerEditDuration',
    components: {

    },
    mixins: [
        interactsWithDisplayersEdit,
    ],
    props: {
    },
    emits: [
        'update:dataValue',
    ],
    data() {
        return {

        };
    },
    computed: {
    },
    methods: {
        updateDataValue({ val, line }) {
            const formatted = _.camelCase(line);
            this.$proxyEvent(val, this.modifiableFieldValue || {}, formatted, 'update:dataValue');
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.c-displayer-edit-duration {

} */

</style>
