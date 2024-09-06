<template>
    <div class="c-displayer-edit-date-range">
        <FromToLine
            class="mb-2"
            format="FROM"
            inputClass="c-displayer-edit-date-range__input"
        >
            <DateInput
                :dateTime="date?.from"
                :showClear="true"
                :dateNullable="true"
                @update:dateTime="updateFromTo($event, 'from')"
            >
            </DateInput>
        </FromToLine>
        <FromToLine
            format="TO"
            inputClass="c-displayer-edit-date-range__input"
        >
            <DateInput
                :dateTime="date?.to"
                :showClear="true"
                :dateNullable="true"
                @update:dateTime="updateFromTo($event, 'to')"
            >
            </DateInput>
        </FromToLine>
    </div>
</template>

<script>

import DateInput from '@/components/dateTimeInputs/DateInput.vue';

import interactsWithDisplayersEdit from '@/vue-mixins/displayers/interactsWithDisplayersEdit.js';
import FromToLine from '@/components/displayers/FromToLine.vue';

export default {
    name: 'DisplayerEditDateRange',
    components: {
        FromToLine,
        DateInput,
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
        date() {
            return this.modifiableFieldValue;
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

.c-displayer-edit-date-range {
    &__input {
        max-width: 180px;
    }
}

</style>
