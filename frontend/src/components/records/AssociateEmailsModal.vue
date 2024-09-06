<template>
    <Modal
        class="o-associate-emails-modal"
        containerClass="o-associate-emails-modal__container p-8"
        v-bind="$attrs"
        @closeModal="$emit('closeModal')"
    >
        <AssociateEmailAddresses
            v-if="viewType === 'FROM_EMAIL'"
            :email="email"
        >
        </AssociateEmailAddresses>

        <AssociateRecordEmails
            v-if="viewType === 'FROM_RECORD'"
            :record="record"
            :suggestedEmailAddresses="suggestedEmailAddresses"
        >
        </AssociateRecordEmails>
    </Modal>
</template>

<script>

import AssociateEmailAddresses from './AssociateEmailAddresses.vue';
import AssociateRecordEmails from '@/components/records/AssociateRecordEmails.vue';

export default {
    name: 'AssociateEmailsModal',
    components: {
        AssociateEmailAddresses,
        AssociateRecordEmails,
    },
    mixins: [
    ],
    props: {
        email: {
            type: [Object, null],
            default: null,
        },
        record: {
            type: [Object, null],
            default: null,
        },
        viewType: {
            type: String,
            default: 'FROM_EMAIL',
            validator(val) {
                return ['FROM_EMAIL', 'FROM_RECORD'].includes(val);
            },
        },
        suggestedEmailAddresses: {
            type: [Array, null],
            default: null,
        },

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

    },
    created() {

    },
};
</script>

<style>

.o-associate-emails-modal {
    &__container {
        width: 500px;
    }
}

</style>
