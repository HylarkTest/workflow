<template>
    <div class="o-preview-line">
        <component
            :is="templateComponent"
            :showNecessaryLabels="true"
            v-bind="$attrs"
        >
        </component>
    </div>
</template>

<script>

export default {
    name: 'PreviewLine',
    components: {

    },
    mixins: [
    ],
    props: {
        templateComponent: {
            type: String,
            required: true,
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

/*.o-preview-line {

} */

</style>
