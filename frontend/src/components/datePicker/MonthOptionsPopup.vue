<template>
    <PopupBasic
        class="c-month-options-popup"
        containerClass="p-1"
        widthProp="10rem"
    >
        <MonthOptions
            :viewedMonth="viewedMonth"
            @selectMonth="$emit('selectMonth', $event)"
        >
        </MonthOptions>
    </PopupBasic>
</template>

<script>

import MonthOptions from './MonthOptions.vue';

export default {
    name: 'MonthOptionsPopup',
    components: {
        MonthOptions,
    },
    mixins: [
    ],
    props: {
        viewedMonth: {
            type: Number,
            required: true,
        },
    },
    emits: [
        'selectMonth',
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

/*.c-month-options-popup {

} */

</style>
