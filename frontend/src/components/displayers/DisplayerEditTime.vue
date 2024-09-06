<template>
    <div class="c-displayer-edit-time">
        <TimeInput
            :dateTime="modifiableFieldValue"
            :showClear="true"
            @update:dateTime="updateDataValue"
        >
        </TimeInput>
    </div>
</template>

<script>
import TimeInput from '@/components/dateTimeInputs/TimeInput.vue';

import interactsWithDisplayersEdit from '@/vue-mixins/displayers/interactsWithDisplayersEdit.js';

export default {
    name: 'DisplayerEditTime',
    components: {
        TimeInput,
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

    },
    created() {
    },
};
</script>

<style scoped>

.c-displayer-edit-time {
    max-width: 200px;
}

</style>
