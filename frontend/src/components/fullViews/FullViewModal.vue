<template>
    <Modal
        class="c-full-view-modal"
        containerClass="w-10/12"
        :containerStyle="{ height: '80vh' }"
        v-bind="$attrs"
    >
        <FullView
            v-bind="$attrs"
        >
        </FullView>
    </Modal>
</template>

<script>

export default {
    name: 'FullViewModal',
    components: {
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

/*.c-full-view-modal {

} */

</style>
