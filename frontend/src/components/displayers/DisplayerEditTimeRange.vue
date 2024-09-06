<template>
    <div class="c-displayer-edit-time-range">
        <FromToLine
            class="mb-2"
            format="FROM"
            inputClass="c-displayer-edit-time-range__input"
        >
            <TimeInput
                :dateTime="modifiableFieldValue?.from"
                :showClear="true"
                @update:dateTime="updateFromTo($event, 'from')"
            >
            </TimeInput>
        </FromToLine>
        <FromToLine
            format="TO"
            inputClass="c-displayer-edit-time-range__input"
        >
            <TimeInput
                :dateTime="modifiableFieldValue?.to"
                :showClear="true"
                @update:dateTime="updateFromTo($event, 'to')"
            >
            </TimeInput>
        </FromToLine>
    </div>
</template>

<script>
import TimeInput from '@/components/dateTimeInputs/TimeInput.vue';
import FromToLine from '@/components/displayers/FromToLine.vue';

import interactsWithDisplayersEdit from '@/vue-mixins/displayers/interactsWithDisplayersEdit.js';

export default {
    name: 'DisplayerEditTimeRange',
    components: {
        TimeInput,
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
    },
    created() {
    },
};
</script>

<style scoped>

.c-displayer-edit-time-range {
    &__input {
        max-width: 200px;
    }
}

</style>
