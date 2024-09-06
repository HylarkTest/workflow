<template>
    <Modal
        class="o-support-contact-modal"
        containerClass="o-support-contact-modal__container p-6"
        v-bind="$attrs"
    >
        <SupportContactForm
            @closeModal="$emit('closeFormModal')"
        >
        </SupportContactForm>
    </Modal>
</template>

<script>

import SupportContactForm from './SupportContactForm.vue';
import Modal from '@/components/dialogs/Modal.vue';

export default {
    name: 'SupportContactModal',
    components: {
        SupportContactForm,
        Modal,
    },
    mixins: [
    ],
    props: {

    },
    emits: [
        'closeFormModal',
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

<style>

.o-support-contact-modal {
    &__container {
        width: 700px;
    }
}

</style>
