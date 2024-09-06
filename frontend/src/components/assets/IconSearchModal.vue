<template>
    <Modal
        class="o-icon-search-modal"
        containerClass="p-6 w-600p"
        v-bind="$attrs"
    >
        <IconSearch
            :selectedIcon="selectedIcon"
            @update:selectedIcon="$emit('update:selectedIcon', $event)"
        >
        </IconSearch>

        <!-- <IconSearchModal
            v-if="isModalOpen"
            :selectedIcon="symbol"
            @update:selectedIcon="selectIcon"
            @closeModal="closeModal"
        >
        </IconSearchModal> -->
    </Modal>
</template>

<script>

import IconSearch from '@/components/assets/IconSearch.vue';

export default {
    name: 'IconSearchModal',
    components: {
        IconSearch,
    },
    mixins: [
    ],
    props: {
        selectedIcon: {
            type: String,
            default: '',
        },
    },
    emits: [
        'update:selectedIcon',
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

/*.o-icon-search-modal {

} */

</style>
