<template>
    <div class="c-displayer-edit-duration-range">
        <FromToLine
            class="mb-2"
        >
            <DurationBoxes
                :duration="modifiableFieldValue?.from"
                v-bind="$attrs"
                @update:duration="updateRange($event, 'from')"
            >
            </DurationBoxes>
        </FromToLine>
        <FromToLine
            format="TO"
        >
            <DurationBoxes
                :duration="modifiableFieldValue?.to"
                v-bind="$attrs"
                @update:duration="updateRange($event, 'to')"
            >
            </DurationBoxes>
        </FromToLine>
    </div>
</template>

<script>

import interactsWithDisplayersEdit from '@/vue-mixins/displayers/interactsWithDisplayersEdit.js';
import FromToLine from '@/components/displayers/FromToLine.vue';

export default {
    name: 'DisplayerEditDurationRange',
    components: {
        FromToLine,
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
        updateRange({ val, line }, key) {
            const formatted = _.camelCase(line);
            const path = `${key}.${formatted}`;
            this.updateDataValue(val, path);
        },
        updateDataValue(val, path) {
            this.$proxyEvent(val, this.modifiableFieldValue || {}, path, 'update:dataValue');
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.c-displayer-edit-duration-range {

} */

</style>
