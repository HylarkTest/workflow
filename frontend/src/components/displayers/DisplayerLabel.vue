<template>
    <div
        class="c-displayer-label"
        :class="styleClass"
    >
        <slot></slot>
    </div>
</template>

<script>

export default {
    name: 'DisplayerLabel',
    components: {

    },
    mixins: [
    ],
    props: {
        styleClass: {
            type: String,
            default: 'label-data--light',
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

/*.c-displayer-label {

} */

</style>
