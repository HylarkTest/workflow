<template>
    <div class="o-preview-kanban centered">
        <div class="w-300p">
            <component
                :is="templateComponent"
                :showNecessaryLabels="true"
                v-bind="$attrs"
            >
            </component>
        </div>
    </div>
</template>

<script>

export default {
    name: 'PreviewKanban',
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

/*.o-preview-kanban {

} */

</style>
