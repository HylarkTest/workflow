<template>
    <PopupBasic
        class="c-date-picker-popup"
        :bottom="true"
        v-bind="$attrs"
    >
        <DatePicker
            v-bind="$attrs"
        >
        </DatePicker>
    </PopupBasic>
</template>

<script>

import DatePicker from '@/components/datePicker/DatePicker.vue';

export default {
    name: 'DatePickerPopup',
    components: {
        DatePicker,
    },
    mixins: [
    ],
    props: {

    },
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

/*.c-date-picker-popup {

} */

</style>
