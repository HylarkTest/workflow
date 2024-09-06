<template>
    <Modal
        class="c-page-settings-template"
        containerClass="p-8 w-1/2"
        v-bind="$attrs"
    >
        <h1 class="header-2 mb-8">
            <i class="fal fa-cog mr-4">
            </i>
            <span
                v-t="'links.settings'"
            >
            </span>
            <span>
                - Todos
            </span>
        </h1>

        <slot>
        </slot>
    </Modal>
</template>

<script>

export default {
    name: 'PageSettingsTemplate',
    components: {
    },
    mixins: [
    ],
    props: {
        typeName: {
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

/*.c-page-settings-template {

} */

</style>
