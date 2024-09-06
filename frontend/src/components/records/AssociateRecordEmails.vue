<template>
    <div
        class="o-associate-record-emails"
        :class="{ unclickable: processing }"
    >
        <h1 class="header-2 text-center mb-2">
            Associate email addresses to
            <span
                class="font-bold text-cm-700"
            >
                {{ recordName }}
            </span>
        </h1>

        <div class="text-sm text-cm-600 bg-cm-100 rounded-xl p-4 mb-4">
            <p>
                Emails exchanged with the associated email addresses will appear in this record's email tab.
            </p>
        </div>

        <div
            v-if="remainingSuggestedLength"
            class="bg-secondary-100 p-4 rounded-lg mb-4"
        >
            <p
                class="font-semibold text-sm mb-0.5"
            >
                Suggested
            </p>

            <p
                class="text-xs mb-2 text-cm-600"
            >
                Click to associate the email address suggestion to {{ recordName }}
            </p>

            <div class="flex flex-wrap gap-1">
                <EmailDisplay
                    v-for="{ record: item, email } in remainingSuggested"
                    :key="email"
                    :class="{ unclickable: hitMax }"
                    recordComponent="button"
                    type="button"
                    :email="email"
                    :record="item"
                    @click="addEmail(email)"
                >
                </EmailDisplay>
            </div>
        </div>

        <div>
            <p
                class="font-semibold mb-1"
            >
                <i
                    class="fa-regular fa-at mr-1"
                >
                </i>
                Associated email addresses
            </p>

            <div class="flex mb-4 items-center">
                <InputBox
                    v-model="typedEmail"
                    class="flex-1"
                    placeholder="Email address"
                    size="sm"
                    bgColor="gray"
                >
                </InputBox>

                <button
                    class="button-rounded--sm ml-2 button-primary--light"
                    :class="{ unclickable: hitMax }"
                    :disabled="hitMax"
                    type="button"
                    @click="addEmail(typedEmail)"
                >
                    Add
                </button>
            </div>

            <div
                v-if="connectedLength"
                class=""
            >
                <div
                    v-for="(group, key) in connectedByAccount"
                    :key="key"
                    class="mb-4 last:mb-0"
                >
                    <div class="text-sm text-primary-700 mb-1 font-semibold">
                        {{ key }}
                    </div>

                    <div class="flex gap-1 flex-wrap">
                        <EmailDisplay
                            v-for="{ account, email, isFromField } in group"
                            :key="email"
                            class="hover:bg-cm-00 transition-2eio"
                            :email="email"
                            :record="isFromField ? record : null"
                            :showClear="true"
                            @removeEmail="removeAssociation(email, account)"
                        >
                        </EmailDisplay>
                    </div>
                </div>
            </div>
        </div>
        <IntegrationSelectionModal
            v-if="isModalOpen"
            :integrations="integrations"
            integrationType="EMAILS"
            @selectIntegration="addEmailWithIntegration"
            @closeModal="closeModal"
        >
        </IntegrationSelectionModal>
    </div>
</template>

<script>

import EmailDisplay from '@/components/records/EmailDisplay.vue';
import IntegrationSelectionModal from '@/components/integrations/IntegrationSelectionModal.vue';

import fetchesEmailIntegrations from '@/vue-mixins/emails/fetchesEmailIntegrations.js';
import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

import { associateEmailAddress, dissociateEmailAddress } from '@/core/repositories/itemRepository.js';

export default {
    name: 'AssociateRecordEmails',
    components: {
        EmailDisplay,
        IntegrationSelectionModal,
    },
    mixins: [
        fetchesEmailIntegrations,
        interactsWithModal,
    ],
    props: {
        record: {
            type: Object,
            required: true,
        },
        suggestedEmailAddresses: {
            type: Array,
            required: true,
        },
    },
    data() {
        return {
            typedEmail: '',
            pendingEmail: null,
            processing: false,
        };
    },
    computed: {
        recordName() {
            return this.record.name;
        },
        connectedEmailAddresses() {
            return this.record.features?.EMAILS_ASSOCIATED_ADDRESSES || [];
        },
        connectedFullEmails() {
            return this.connectedEmailAddresses.map((emailObj) => {
                const isFromField = !!_.find(this.suggestedEmailAddresses, { email: emailObj.email });
                return {
                    ...emailObj,
                    isFromField,
                };
            });
        },
        connectedByAccount() {
            return _.groupBy(this.connectedFullEmails, 'account.accountName');
        },
        connectedLength() {
            return this.connectedEmailAddresses.length;
        },
        remainingSuggested() {
            return _.differenceBy(this.suggestedEmailAddresses, this.connectedEmailAddresses, 'email');
        },
        remainingSuggestedLength() {
            return this.remainingSuggested?.length;
        },
        hitMax() {
            return this.connectedLength >= 5;
        },
    },
    methods: {
        addEmail(email) {
            if (this.hitMax) {
                return;
            }
            if (this.emailIntegrationsLength) {
                this.pendingEmail = email;
                this.openModal();
            } else {
                this.addAssociation(email);
            }
        },
        addEmailWithIntegration(integration) {
            this.closeModal();
            this.addAssociation(this.pendingEmail, integration);
            this.pendingEmail = null;
        },
        async addAssociation(address, selectedIntegration = null) {
            const integration = selectedIntegration || this.emailIntegrations[0];
            this.processing = true;
            try {
                await associateEmailAddress(address, this.record, integration);
                this.typedEmail = '';
            } finally {
                this.processing = false;
            }
        },
        async removeAssociation(address, selectedIntegration = null) {
            const integration = selectedIntegration || this.emailIntegrations[0];
            this.processing = true;
            try {
                await dissociateEmailAddress(address, this.record, integration);
                this.typedEmail = '';
            } finally {
                this.processing = false;
            }
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-associate-record-emails {

} */

</style>
