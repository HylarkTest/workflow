<template>
    <div class="c-displayer-edit-date">

        <DateInput
            v-model:dateTime="date"
            :showClear="true"
            :dateNullable="true"
        >
        </DateInput>
    </div>
</template>

<script>

import DateInput from '@/components/dateTimeInputs/DateInput.vue';

import interactsWithDisplayersEdit from '@/vue-mixins/displayers/interactsWithDisplayersEdit.js';

export default {
    name: 'DisplayerEditDate',
    components: {
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
        date: {
            get() {
                return this.modifiableFieldValue;
            },
            set(val) {
                this.updateDataValue(val);
            },
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

.c-displayer-edit-date {
    max-width: 180px;
}

</style>
