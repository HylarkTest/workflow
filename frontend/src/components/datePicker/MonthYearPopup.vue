<template>
    <PopupBasic
        class="c-month-year-popup"
        widthProp="11.25rem"
    >
        <MonthYearPicker
            v-bind="$attrs"
        >
        </MonthYearPicker>
    </PopupBasic>
</template>

<script>

import MonthYearPicker from './MonthYearPicker.vue';

export default {
    name: 'MonthYearPopup',
    components: {
        MonthYearPicker,
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

/*.c-month-year-popup {

} */

</style>
