<template>
    <div class="c-close-button rounded-full">
        <IconHover
            :class="buttonSizeClass"
            icon="far fa-times"
            v-bind="$attrs"
        >
        </IconHover>
    </div>
</template>

<script>

import IconHover from '@/components/buttons/IconHover.vue';

export default {
    name: 'CloseButton',
    components: {
        IconHover,
    },
    mixins: [
    ],
    props: {
        buttonSizeClass: {
            type: String,
            default: 'c-icon-hover--sm',
        },
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

/*.c-close-button {

} */

</style>
