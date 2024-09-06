<template>
    <ConfirmModal
        class="o-group-item-delete"
        icon="fal fa-tag"
        v-bind="$attrs"
        @closeModal="$emit('closeModal')"
        @cancelAction="$emit('closeModal')"
        @proceedWithAction="$emit('deleteItem')"
    >
        <p class="mb-3">
            This item may be in use. It will be removed from any data to which it is added.
        </p>

        <p class="mb-3">
            {{ $t('common.wishToContinue') }}
        </p>
    </ConfirmModal>
</template>

<script>

import ConfirmModal from '@/components/assets/ConfirmModal.vue';

export default {
    name: 'GroupItemDelete',
    components: {
        ConfirmModal,
    },
    mixins: [
    ],
    props: {
        item: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'closeModal',
        'deleteItem',
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

/*.o-group-item-delete {

} */

</style>
