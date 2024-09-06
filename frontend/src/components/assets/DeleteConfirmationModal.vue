<template>
    <div class="o-confirm-delete-modal">
        <ConfirmModal
            icon="fal fa-trash-alt"
            @closeModal="cancelDelete"
            @cancelAction="cancelDelete"
            @proceedWithAction="$emit('deleteItem')"
        >
            {{ deleteConfirmation }}
        </ConfirmModal>
    </div>
</template>

<script>

import ConfirmModal from '@/components/assets/ConfirmModal.vue';

export default {
    name: 'ConfirmDeleteModal',
    components: {
        ConfirmModal,
    },
    mixins: [
    ],
    props: {
        itemName: {
            type: String,
            default: '',
        },
    },
    emits: [
        'cancelDelete',
        'deleteItem',
    ],
    data() {
        return {

        };
    },
    computed: {
        deleteConfirmation() {
            if (this.itemName) {
                return this.$t('warnings.deleteItemConfirmation', { itemName: this.itemName });
            }
            return this.$t('warnings.deleteConfirmation');
        },
    },
    methods: {
        cancelDelete() {
            this.$emit('cancelDelete');
        },
    },
    created() {
    },
};
</script>

<style scoped>
/* .o-confirm-delete-modal {

} */
</style>
