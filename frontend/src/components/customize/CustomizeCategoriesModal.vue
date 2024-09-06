<template>
    <Modal
        class="o-customize-categories-modal"
        containerClass="p-8 w-[700px]"
        @closeModal="closeModal"
    >
        <CustomizeCategories>
        </CustomizeCategories>
    </Modal>
</template>

<script>

import CustomizeCategories from './CustomizeCategories.vue';

export default {
    name: 'CustomizeCategoriesModal',
    components: {
        CustomizeCategories,
    },
    mixins: [
    ],
    props: {

    },
    emits: [
        'closeModal',
    ],
    data() {
        return {

        };
    },
    computed: {

    },
    methods: {
        closeModal() {
            this.$emit('closeModal');
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-customize-categories-modal {

} */

</style>
