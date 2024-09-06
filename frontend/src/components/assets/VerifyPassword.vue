<template>
    <ConfirmModal
        icon="fal fa-lock-keyhole"
        headerTextPath="This action requires password verification"
        actionButtonTextPath="common.verify"
        actionButtonColorClasses="bg-primary-600 hover:bg-primary-500"
        v-bind="$attrs"
        @proceedWithAction="verifyPassword"
    >
        <FormWrapper
            :form="form"
        >
            <InputPassword
                formField="password"
                name="password"
                :labelPath="false"
                :placeholder="$t('labels.yourPassword')"
            >
            </InputPassword>
        </FormWrapper>
    </ConfirmModal>
</template>

<script>

import ConfirmModal from '@/components/assets/ConfirmModal.vue';

export default {
    name: 'VerifyPassword',
    components: {
        ConfirmModal,
    },
    mixins: [
    ],
    props: {
        processing: Boolean,
    },
    emits: [
        'verifyPassword',
    ],
    data() {
        return {
            form: this.$form({
                password: '',
            }),
        };
    },
    computed: {

    },
    methods: {
        verifyPassword() {
            this.$emit('verifyPassword', this.form);
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.c-verify-password {

} */

</style>
