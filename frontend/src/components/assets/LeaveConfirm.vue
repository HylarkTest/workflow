<template>
    <ConfirmModal
        icon="fal fa-person-to-door"
        headerTextPath="validation.unsavedInformation"
        actionButtonTextPath="common.leave"
        actionButtonColorClasses="bg-secondary-600 hover:bg-secondary-500"
        v-bind="$attrs"
        @proceedWithAction="leaveScreen"
    >
        <slot>
        </slot>
    </ConfirmModal>
</template>

<script>

import ConfirmModal from '@/components/assets/ConfirmModal.vue';

export default {
    name: 'LeaveConfirm',
    components: {
        ConfirmModal,
    },
    mixins: [
    ],
    props: {
    },
    emits: [
        'leaveScreen',
    ],
    data() {
        return {
        };
    },
    computed: {

    },
    methods: {
        leaveScreen() {
            this.$emit('leaveScreen');
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.c-leave-confirm {

} */

</style>
