<template>
    <div class="c-displayer-edit-date-time-range">
        <FromToLine
            class="mb-2"
            format="FROM"
        >
            <div class="flex flex-wrap">
                <DateTimeInput
                    :dateTime="from"
                    :timeOptionsProp="{ forceTime: true, forceDate: true }"
                    :showClear="true"
                    @update:dateTime="updateFromTo($event, 'from')"
                >
                </DateTimeInput>
            </div>
        </FromToLine>
        <FromToLine
            format="TO"
        >
            <div class="flex-flex-wrap">
                <DateTimeInput
                    :dateTime="to"
                    :timeOptionsProp="{ forceTime: true }"
                    :showClear="true"
                    @update:dateTime="updateFromTo($event, 'to')"
                >
                </DateTimeInput>
            </div>
        </FromToLine>
    </div>
</template>

<script>

import DateTimeInput from '@/components/dateTimeInputs/DateTimeInput.vue';

import interactsWithDisplayersEdit from '@/vue-mixins/displayers/interactsWithDisplayersEdit.js';
import FromToLine from '@/components/displayers/FromToLine.vue';

export default {
    name: 'DisplayerEditDateTimeRange',
    components: {
        FromToLine,
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
        from() {
            return this.modifiableFieldValue?.from || null;
        },
        to() {
            return this.modifiableFieldValue?.to || null;
        },
    },
    methods: {

    },
    created() {
    },
};
</script>

<style scoped>

/*.c-displayer-edit-date-time-range {

}*/

</style>
