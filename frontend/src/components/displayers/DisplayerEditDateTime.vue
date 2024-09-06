<template>
    <div class="c-displayer-edit-date-time flex flex-wrap">

        <DateTimeInput
            v-model:dateTime="dateTime"
            :timeOptionsProp="{ forceTime: true, forceDate: true }"
            :showClear="true"
        >
        </DateTimeInput>
    </div>
</template>

<script>

import DateTimeInput from '@/components/dateTimeInputs/DateTimeInput.vue';

import interactsWithDisplayersEdit from '@/vue-mixins/displayers/interactsWithDisplayersEdit.js';

export default {
    name: 'DisplayerEditDateTime',
    components: {
        DateTimeInput,
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
        dateTime: {
            get() {
                return this.modifiableFieldValue || null;
            },
            set(dateTime) {
                this.updateDataValue(dateTime);
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

/*.c-displayer-edit-date-time {

}*/

</style>
