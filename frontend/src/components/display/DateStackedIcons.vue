<template>
    <StackedIcons
        class="c-date-stacked-icons"
        mainIcon="fa-clock"
        :secondaryIcon="secondaryIcon"
        v-bind="$attrs"
    >
    </StackedIcons>
</template>

<script>

import StackedIcons from './StackedIcons.vue';

export default {
    name: 'DateStackedIcons',
    components: {
        StackedIcons,
    },
    mixins: [
    ],
    props: {
        dateType: {
            type: String,
            default: 'CREATED',
            validator(val) {
                return ['CREATED', 'UPDATED'].includes(val);
            },
        },
    },
    data() {
        return {

        };
    },
    computed: {
        secondaryIcon() {
            return this.dateType === 'CREATED' ? 'fa-plus' : 'fa-pencil';
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.c-date-stacked-icons {

} */

</style>
