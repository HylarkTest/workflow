<template>
    <div
        class="o-email-write"
        :class="deleteProcessingClass"
    >
        <h2
            v-t="headerPath"
            class="header-2 mb-4"
        >
        </h2>

        <FormWrapper
            class="flex-1 flex flex-col"
            :form="form"
            @submit="sendEmail"
        >
            <div class="flex-1 flex flex-col">
                <div
                    v-if="!mainIntegrationId && hasMultipleIntegrations"
                    class="bg-secondary-100 p-4 rounded-lg mb-4"
                >
                    <p
                        class="text-xssm text-medium mb-1"
                    >
                        Select the integration you want to use to send this email
                    </p>

                    <div class="flex items-center">
                        <label
                            class="text-xssm mr-2 font-semibold"
                        >
                            From:
                        </label>

                        <DropdownBox
                            v-model="form.sourceId"
                            class="flex-1"
                            placeholder="Integration"
                            property="id"
                            :options="integrationsForEmails"
                            :displayRule="integrationsName"
                            size="sm"
                        >
                        </DropdownBox>
                    </div>
                </div>
                <div
                    v-if="(mainIntegrationId && !mailbox) || !hasMultipleIntegrations"
                    class="bg-secondary-100 py-2 px-4 rounded-lg mb-4 text-xs"
                >
                    <span
                        class="font-semibold"
                    >
                        From:
                    </span>
                    {{ fullSelectedIntegration?.accountName }}
                </div>

                <div class="mb-4">
                    <EntitiesPicker
                        v-model="form.associations"
                        bgColor="gray"
                        :withFeatures="['EMAILS']"
                        :entityVal="null"
                        placeholder="Associate this message"
                    >
                    </EntitiesPicker>

                    <p class="italic text-xxs leading-tight">
                        If you are sending this email to an email address that is associated with a record,
                        you do not need to add a specific association to that record.
                    </p>

                    <div
                        v-if="associationsLength"
                        class="mt-2 flex gap-1 flex-wrap"
                    >
                        <ConnectedRecord
                            v-for="association in form.associations"
                            :key="association.id"
                            :item="association"
                            bgColor="gray"
                            :showClear="true"
                            @removeItem="removeAssociation"
                            @click.stop
                        >

                        </ConnectedRecord>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="flex justify-end">
                        <button
                            class="o-email-write__button bg-cm-100 mr-2 hover:bg-cm-200"
                            :class="showsCc ? 'opacity-0' : 'opacity-100'"
                            type="button"
                            @click="toggleOption('CC')"
                        >
                            Cc
                        </button>

                        <button
                            class="o-email-write__button bg-cm-100 hover:bg-cm-200"
                            :class="showsBcc ? 'opacity-0' : 'opacity-100'"
                            type="button"
                            @click="toggleOption('BCC')"
                        >
                            Bcc
                        </button>
                    </div>
                    <div class="mb-2">
                        <PersonEmailFinder
                            v-model:selectedRecords="form.to"
                            placeholder="To"
                            inputComponent="InputBox"
                            bgColor="gray"
                            :error="form.errors().getFirst('to')"
                        >
                        </PersonEmailFinder>

                        <div
                            v-if="suggestedLength && isCompose"
                            class="bg-secondary-100 py-2 px-4 rounded-lg mt-1 mb-3 text-xs"
                        >
                            <p
                                class="font-semibold mb-1"
                            >
                                Suggested
                            </p>

                            <div class="flex flex-wrap gap-1">
                                <EmailDisplay
                                    v-for="{ record, email: emailAddress, account } in remainingAddresses"
                                    :key="emailAddress"
                                    class="hover:bg-cm-00 transition-2eio"
                                    recordComponent="button"
                                    type="button"
                                    :email="emailAddress"
                                    :record="record"
                                    @click="addSuggested(record, emailAddress)"
                                >
                                    <i
                                        v-if="account"
                                        class="fa-regular fa-at text-cm-400 ml-0.5"
                                    >
                                    </i>
                                </EmailDisplay>
                            </div>
                        </div>
                    </div>
                    <div
                        v-show="showsCc"
                        class="mb-2 flex items-center"
                    >
                        <PersonEmailFinder
                            v-model:selectedRecords="form.cc"
                            class="flex-1"
                            placeholder="Cc"
                            inputComponent="InputBox"
                            bgColor="gray"
                            @clearInput="removeOption('CC')"
                        >
                        </PersonEmailFinder>

                        <ClearButton
                            positioningClass="ml-2"
                            @click="toggleOption('CC')"
                        >
                        </ClearButton>
                    </div>
                    <div
                        v-show="showsBcc"
                        class="mb-2 flex items-center"
                    >
                        <PersonEmailFinder
                            v-model:selectedRecords="form.bcc"
                            class="flex-1"
                            placeholder="Bcc"
                            inputComponent="InputBox"
                            bgColor="gray"
                            @clearInput="removeOption('BCC')"
                        >
                        </PersonEmailFinder>

                        <ClearButton
                            positioningClass="ml-2"
                            @click="toggleOption('BCC')"
                        >
                        </ClearButton>
                    </div>
                    <div>
                        <InputBox
                            formField="subject"
                            placeholder="Subject"
                            bgColor="gray"
                        >
                        </InputBox>
                    </div>
                </div>

                <div class="mb-2">
                    <AttachmentButton
                        ref="attachButton"
                        @addFile="addFile"
                    >
                        <button
                            class="o-email-write__button bg-secondary-100 hover:bg-secondary-200 mr-2"
                            type="button"
                            @click="attachFile"
                        >
                            <i
                                class="fal fa-paperclip mr-1"
                            >
                            </i>
                            Attach a file
                        </button>
                    </AttachmentButton>

                    <div class="flex flex-wrap gap-1">
                        <AttachmentDisplay
                            v-for="(file, index) in explicitAttachments"
                            :key="file.id || index"
                            :file="file.name ? file : file.file"
                            :index="index"
                            @removeAttachment="removeAttachment"
                        >
                        </AttachmentDisplay>
                    </div>

                    <!-- <button
                        class="o-email-write__button bg-secondary-100 hover:bg-secondary-200"
                        type="button"
                    >
                        <i
                            class="fal fa-signature mr-1"
                        >
                        </i>
                        Add signature
                    </button> -->
                </div>

                <div class="mb-4 flex-1">
                    <TipTapInput
                        formField="tiptap"
                        bgColor="gray"
                    >
                    </TipTapInput>
                </div>
            </div>

            <div class="flex justify-end mb-2">
                <div
                    v-if="draftTime"
                    class="text-xssm flex items-center text-cm-500"
                >
                    <i
                        class="far fa-floppy-disk mr-2"
                    >
                    </i>

                    Draft saved at <span class="ml-0.5 font-semibold">{{ draftTime }}</span>
                </div>
            </div>

            <div class="flex items-baseline justify-between mb-8">

                <div class="flex">
                    <button
                        v-t="'common.send'"
                        class="button bg-primary-600 text-cm-00 hover:bg-primary-500 mr-2"
                        :class="{ unclickable: isSendDisabled }"
                        type="submit"
                        :disabled="isSendDisabled"
                    >

                    </button>
                </div>

                <DeleteButton
                    v-if="form.fromDraft"
                    class="ml-1"
                    buttonComponent="IconButton"
                    :title="$t('common.discard')"
                    @click="discardDraft"
                >
                </DeleteButton>
            </div>
        </FormWrapper>
        <FullLoaderProcessing
            v-if="processing"
            positionClass="absolute"
        >
            <div class="font-bold text-primary-600 z-over text-2xl mb-20">
                Sending...
            </div>
        </FullLoaderProcessing>

        <ConfirmModal
            v-if="isModalOpen"
            :headerTextPath="$t('common.areYouSure')"
            @closeModal="closeModal"
            @cancelAction="closeModal"
            @proceedWithAction="deleteDraft"
        >
            <p class="mb-3">
                {{ deleteConfirmText }}
            </p>
        </ConfirmModal>
    </div>
</template>

<script>
import { generateJSON } from '@tiptap/core';
import tiptapExtensions from '@/tiptap/extensions/index.js';

import FullLoaderProcessing from '@/components/loaders/FullLoaderProcessing.vue';
import PersonEmailFinder from '@/components/assets/PersonEmailFinder.vue';
import DeleteButton from '@/components/buttons/DeleteButton.vue';
import AttachmentDisplay from '@/components/assets/AttachmentDisplay.vue';
import TipTapInput from '@/tiptap/TipTapInput.vue';
import ConfirmModal from '@/components/assets/ConfirmModal.vue';
import ClearButton from '@/components/buttons/ClearButton.vue';
import EmailDisplay from '@/components/records/EmailDisplay.vue';
import EntitiesPicker from '@/components/pickers/EntitiesPicker.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';
import assistsComponentAroundAttachmentButton
    from '@/vue-mixins/common/assistsComponentAroundAttachmentButton.js';
import fetchesEmailIntegrations from '@/vue-mixins/emails/fetchesEmailIntegrations.js';

import Mailbox from '@/core/models/Mailbox.js';
import { createEmail, deleteEmail, saveDraft } from '@/core/repositories/emailRepository.js';
import { arrRemoveId } from '@/core/utils.js';
import { associateEmailAddress } from '@/core/repositories/itemRepository.js';

export default {
    name: 'EmailWrite',
    components: {
        TipTapInput,
        DeleteButton,
        PersonEmailFinder,
        AttachmentDisplay,
        FullLoaderProcessing,
        ConfirmModal,
        ClearButton,
        EmailDisplay,
        EntitiesPicker,
    },
    mixins: [
        interactsWithModal,
        assistsComponentAroundAttachmentButton,
        fetchesEmailIntegrations,
    ],
    props: {
        mailbox: {
            type: [Mailbox, null],
            default: null,
        },
        email: {
            type: [Object, null],
            default: () => (null),
        },
        action: {
            type: String,
            required: true,
            validator(val) {
                return ['DRAFT', 'COMPOSE', 'REPLY', 'REPLY_ALL', 'FORWARD'].includes(val);
            },
        },
        toEmailAddresses: {
            type: [Array, null],
            default: null,
        },
        suggestedEmailAddresses: {
            type: [Array, null],
            default: null,
        },
        lastUsedIntegration: {
            type: [String, null],
            default: '',
        },
        emailAddressesForAssociation: {
            type: [Array, null],
            default: null,
        },
        node: {
            type: [Object, null],
            default: null,
        },
    },
    emits: [
        'closeEmail',
    ],
    data() {
        const sourceId = this.getIntegrationId();
        return {
            deleteProcessing: false,
            processing: false,
            draftTime: null,
            showOptions: {
                CC: this.email?.cc?.length,
                BCC: this.email?.bcc?.length,
            },
            form: this.$apolloForm({
                sourceId,
                to: this.email ? this.getTo() : this.getDefaultTo(),
                cc: this.email ? this.getCc() : [],
                bcc: this.email ? this.getBcc() : [],
                subject: this.email ? this.getSubject() : '',
                tiptap: this.getTiptapContentFromEmail(),
                fromDraft: this.action === 'DRAFT' ? this.email.id : null,
                attachments: this.email ? this.getAttachments() : [],
                associations: [],
            }),
            interval: null,
            draftMailboxId: this.action === 'DRAFT' ? this.email.mailbox.id : null,
        };
    },
    computed: {
        explicitAttachments() {
            return this.form.attachments?.filter((attachment) => !attachment.isInline);
        },
        isSendDisabled() {
            return this.noRecipient || !this.form.sourceId;
        },
        associationsLength() {
            return this.form.associations.length;
        },
        remainingAddresses() {
            return _.differenceBy(this.suggestedEmailAddresses, this.form.to, 'email');
        },
        suggestedLength() {
            return this.remainingAddresses?.length || 0;
        },
        fullSelectedIntegration() {
            return _.find(this.integrations, { id: this.form.sourceId });
        },
        hasMultipleIntegrations() {
            return this.emailIntegrationsLength > 1;
        },
        mainIntegrationId() {
            return this.getMainIntegrationId();
        },
        deleteProcessingClass() {
            return { unclickable: this.deleteProcessing };
        },
        isNewEmail() {
            return this.action === 'COMPOSE';
        },
        headerPath() {
            if (this.isNewEmail) {
                return 'emails.newMessage';
            }
            return `emails.actions.${_.camelCase(this.action)}`;
        },
        showSaved() {
            return true;
        },
        showsCc() {
            return this.showOptions.CC;
        },
        showsBcc() {
            return this.showOptions.BCC;
        },
        deleteConfirmText() {
            return 'Deleting this draft will delete it from your integrated mailbox.';
        },
        noRecipient() {
            return !(this.toLength || this.ccLength || this.bccLength);
        },
        toLength() {
            return this.form.to.length;
        },
        ccLength() {
            return this.form.cc.length;
        },
        bccLength() {
            return this.form.bcc.length;
        },
        isCompose() {
            return this.action === 'COMPOSE';
        },
        allRecipients() {
            const arr = [...this.form.to, ...this.form.bcc, ...this.form.cc];

            const emails = arr.map((item) => {
                return item.email;
            });
            return _.uniq(emails);
        },
        whichEmailsAssociate() {
            return _.intersection(this.allRecipients, this.emailAddressesForAssociation);
        },
        whichEmailsAssociateLength() {
            return this.whichEmailsAssociate.length;
        },
        emailHasContent() {
            if (_.isNull(this.form.tiptap)) {
                return false;
            }

            return !!this.form.tiptap.content.find((rootblock) => {
                return rootblock.content.find((node) => _.has(node, 'content'));
            });
        },
    },
    methods: {
        discardDraft() {
            return this.openModal();
        },
        async deleteDraft() {
            this.closeModal();
            this.deleteProcessing = true;
            try {
                await deleteEmail(this.form.sourceId, this.draftMailboxId, this.form.fromDraft);
                this.$emit('closeEmail');
            } finally {
                this.deleteProcessing = false;
            }
        },
        toggleOption(type) {
            const state = this.showOptions[type];
            this.showOptions[type] = !state;
        },
        removeOption(type) {
            this.toggleOption(type);
        },
        async sendEmail() {
            this.processing = true;
            try {
                await createEmail(this.form);
                if (this.node && this.whichEmailsAssociateLength) {
                    await Promise.all(this.whichEmailsAssociate.map((email) => {
                        return associateEmailAddress(email, this.node, { id: this.form.sourceId });
                    }));
                }
                this.$successFeedback();
                this.$emit('closeEmail');
            } finally {
                this.processing = false;
            }
        },
        addFile(file) {
            this.form.attachments.push({ file });
        },
        removeAttachment(index) {
            this.form.attachments.splice(index, 1);
        },
        getTo() {
            if (this.action === 'FORWARD') {
                return [];
            }
            if (this.action === 'REPLY') {
                return [this.getEmailObject(this.email.from)];
            }
            if (this.action === 'REPLY_ALL') {
                const addresses = this.email.to.concat(this.email.from);
                const unique = _.uniqBy(addresses, 'address');
                return this.getEmailAddresses(unique);
            }
            return [];
        },
        getDefaultTo() {
            if (this.toEmailAddresses) {
                return this.toEmailAddresses;
            }
            return [];
        },
        getCc() {
            if (this.action === 'REPLY_ALL') {
                return this.getEmailAddresses(this.email.cc);
            }
            return [];
        },
        getBcc() {
            return [];
        },
        getSubject() {
            if (this.action === 'DRAFT') {
                return this.email.subject || '';
            }
            return `Re: ${this.email.subject}`;
        },
        getAttachments() {
            if (this.email.attachments) {
                return this.email.attachments.map((attachment) => ({
                    name: attachment.name,
                    link: attachment.link,
                    isInline: attachment.isInline,
                    contentId: attachment.contentId,
                }));
            }
            return [];
        },
        getContent() {
            return this.email.html;
        },
        getEmailObject(val) {
            return { email: val.address };
        },
        getEmailAddresses(addresses) {
            const validAddresses = addresses.filter((address) => {
                return address.address !== this.mailbox.account?.accountName;
            });
            return validAddresses.map((item) => {
                return this.getEmailObject(item);
            });
        },
        getIntegrationId() {
            return this.getMainIntegrationId()
                || this.lastUsedIntegration
                || null;
        },
        getMainIntegrationId() {
            return this.mailbox?.account?.id
                || this.email?.account.id;
        },
        addSuggested(record, email) {
            this.form.to.push({ record, email });
        },
        removeAssociation(item) {
            this.form.associations = arrRemoveId(this.form.associations, item.id);
        },
        getTiptapContentFromEmail() {
            if (this.email && this.email.html) {
                const html = this.email.htmlWithInlineAttachments();
                if (this.action === 'DRAFT') {
                    return generateJSON(html, tiptapExtensions);
                }
                const newHtml = '<br><p>---------------</p><br>';
                const fullHtml = `${newHtml} ${html}`;
                return generateJSON(fullHtml, tiptapExtensions);
            }
            return null;
        },
        async saveDraft() {
            if (this.emailHasContent) {
                const response = await saveDraft(this.form);
                this.form.fromDraft = response.id;
                this.draftMailboxId = response.mailbox.id;
                this.draftTime = utils.formattedTime(this.$dayjs());
            }
        },
    },
    watch: {
        integrationsForEmails: {
            handler(integrations) {
                if (integrations && integrations.length === 1) {
                    this.form.sourceId = integrations[0].id;
                }
            },
            immediate: true,
        },
    },
    created() {
        this.integrationsName = (integration) => integration.accountName;

        this.interval = setInterval(async () => {
            this.saveDraft();
        }, 60_000);
    },
    unmounted() {
        clearInterval(this.interval);
    },
    mounted() {
    },
};
</script>

<style>

.o-email-write {
    @apply
        flex
        flex-col
        min-h-full
        relative
    ;

    &__button {
        transition: 0.2s ease-in-out;

        @apply
            px-2
            py-0.5
            rounded-full
            text-xs
        ;
    }
}

</style>
