<template>
    <div
        class="o-support-contact-form relative"
        @dragenter="onDragEnter"
        @dragover="onDragEnter"
        @dragleave="onDragLeave"
        @drop="onDrop"
    >

        <AttachmentsOverlay
            :hovering="hovering"
            :accept-multiples="acceptMultiples"
        >
        </AttachmentsOverlay>

        <div class="mb-4">
            <h1
                class="text-2xl font-bold mb-1"
            >
                Contact us
            </h1>
            <p
                class="text-gray-600 text-sm"
            >
                Fill out the form below and we'll get back to you as soon as possible!
            </p>
        </div>

        <FormWrapper
            :form="form"
            @submit="submit"
        >
            <div
                class="mb-6 rounded-lg bg-primary-100 px-4 py-2"
            >
                <div class="text-sm">
                    <div
                        v-if="isSubscribed"
                        class="flex justify-end items-center text-xs font-semibold text-cm-900"
                    >
                        <i class="fas fa-star text-gold-600 mr-1">
                        </i>
                        Subscriber priority
                    </div>
                    <p class="font-semibold text-primary-600">
                        {{ user.name }}
                    </p>
                    <p class="text-xssm text-cm-700">
                        {{ user.email }}
                    </p>
                </div>
            </div>

            <div class="mb-6">
                <label class="o-support-contact-form__label">
                    Query type
                </label>
                <div
                    class="flex justify-center flex-wrap gap-3"
                >
                    <ButtonEl
                        v-for="queryType in queryTypes"
                        :key="queryType.val"
                        class="flex flex-col items-center w-24"
                        @click="setQueryType(queryType.val)"
                    >
                        <div
                            class="centered mb-1 w-10 h-10 rounded-full shadow-lg transition-2eio"
                            :class="queryClasses(queryType.val)"
                        >
                            <i
                                class="fa-regular"
                                :class="queryType.icon"
                            >
                            </i>
                        </div>

                        <p
                            v-t="getCategoryNamePath(queryType.val)"
                            class="text-xs font-medium"
                        >
                        </p>
                    </ButtonEl>
                </div>
            </div>

            <div class="mb-6">
                <label class="o-support-contact-form__label">
                    Subject*
                </label>

                <InputBox
                    bgColor="gray"
                    size="md"
                    formField="subject"
                >
                </InputBox>
            </div>
            <div class="mb-6">
                <label class="o-support-contact-form__label">
                    Message*
                </label>

                <TextareaField
                    bgColor="gray"
                    boxStyle="plain"
                    size="md"
                    formField="description"
                    :placeholder="messagePlaceholders"
                >
                </TextareaField>
            </div>

            <div class="mb-6 relative">
                <label class="o-support-contact-form__label">
                    Attachments (max 5)
                </label>

                <div>
                    <div class="inline-flex">
                        <DragOrAdd
                            :class="{ unclickable: reachedMax }"
                            size="sm"
                            :horizontal="true"
                            :disabled="reachedMax"
                            :acceptMultiples="acceptMultiples"
                            :maxAttachments="maxAttachments"
                            @addFile="addFile"
                        >
                        </DragOrAdd>
                    </div>

                    <div
                        v-if="attachmentsLength"
                        class="flex flex-wrap gap-1 mt-2"
                    >
                        <AttachmentDisplay
                            v-for="(file, index) in formAttachments"
                            :key="file.id || index"
                            :file="file"
                            :index="index"
                            @removeAttachment="removeAttachment"
                        >
                        </AttachmentDisplay>
                    </div>
                </div>
                <AlertTooltip
                    v-if="form.errors().has('attachments[]')"
                >
                    {{ form.errors().getFirst('attachments[]') }}
                </AlertTooltip>
            </div>

            <div
                class="flex justify-end"
            >
                <button
                    class="button--lg button-primary"
                    :class="{ unclickable: !canSendForm }"
                    :disabled="!canSendForm"
                    type="submit"
                >
                    Send
                </button>
            </div>
        </FormWrapper>

        <FullLoaderProcessing
            v-if="processing"
            positionClass="absolute"
            heightWidthClass="h-6 w-6 mx-2"
        >
            <div class="font-bold text-primary-600 z-over text-2xl mb-20">
                Sending...
            </div>
        </FullLoaderProcessing>
    </div>
</template>

<script>

import FullLoaderProcessing from '@/components/loaders/FullLoaderProcessing.vue';
import DragOrAdd from '@/components/documents/DragOrAdd.vue';
import AttachmentDisplay from '@/components/assets/AttachmentDisplay.vue';
import AttachmentsOverlay from '@/components/documents/AttachmentsOverlay.vue';

import interactsWithFileDrop from '@/vue-mixins/interactsWithFileDrop.js';
import AlertTooltip from '@/components/popups/AlertTooltip.vue';
import { createTicket } from '@/core/repositories/supportRepository.js';

const queryTypes = [
    {
        val: 'QUESTION',
        icon: 'fa-message-question',
    },
    {
        val: 'FEATURE_REQUEST',
        icon: 'fa-face-smile-plus',
    },
    {
        val: 'REPORT_BUG',
        icon: 'fa-bug',
    },
    {
        val: 'FEEDBACK',
        icon: 'fa-comment-lines',
    },
    {
        val: 'MY_ACCOUNT',
        icon: 'fa-user-shield',
    },
    {
        val: 'OTHER',
        icon: 'fa-square-envelope',
    },
];

const feedbackInfo = {
    customMessagePath: 'Thank you for getting in touch. We will respond as soon as possible.',
    customHeaderPath: 'Your message has been sent!',
    customIcon: 'far fa-mailbox-flag-up',
};

export default {
    name: 'SupportContactForm',
    components: {
        AlertTooltip,
        FullLoaderProcessing,
        DragOrAdd,
        AttachmentDisplay,
        AttachmentsOverlay,
    },
    mixins: [
        interactsWithFileDrop,
    ],
    props: {

    },
    emits: [
        'closeModal',
    ],
    data() {
        const user = this.$root.authenticatedUser;
        const priority = user.isSubscribed ? 1 : 3;
        return {
            user,
            form: this.$form({
                type: 'QUESTION',
                email: user.email,
                name: user.name,
                subject: '',
                description: '',
                attachments: [],
                priority,
            }),
            processing: false,
            processingAttachment: false,
            querySent: false,
        };
    },
    computed: {
        acceptMultiples() {
            return true;
        },
        maxAttachments() {
            return this.remainingAttachments;
        },
        isSubscribed() {
            return this.user.isSubscribed;
        },
        canSendForm() {
            return this.form.subject
                && this.form.description
                && !this.processingAttachment;
        },
        formType() {
            return this.form.type;
        },
        formAttachments() {
            return this.form.attachments;
        },
        attachmentsLength() {
            return this.formAttachments.length;
        },
        formTypeFormatted() {
            return _.camelCase(this.formType);
        },
        // subjectPlaceholders() {
        //     return this.$t(`support.contact.subjectPlaceholders.${this.formTypeFormatted}`);
        // },
        messagePlaceholders() {
            return this.$t(`support.contact.messagePlaceholders.${this.formTypeFormatted}`);
        },
        reachedMax() {
            return this.attachmentsLength >= 5;
        },
        remainingAttachments() {
            return 5 - this.attachmentsLength;
        },
    },
    methods: {
        getCategoryNamePath(val) {
            const formatted = _.camelCase(val);
            return `support.queryTypes.${formatted}`;
        },
        setQueryType(val) {
            this.form.type = val;
        },
        isQueryType(val) {
            return this.form.type === val;
        },
        queryClasses(val) {
            return this.isQueryType(val)
                ? 'bg-primary-600 text-cm-00'
                : 'text-primary-700 hover:bg-primary-100';
        },
        async submit() {
            this.processing = true;
            try {
                await createTicket(this.form);
                this.$saveFeedback(feedbackInfo, 8000);
                this.closeModal();
            } finally {
                this.processing = false;
            }
        },
        addFile(file) {
            if (_.isArray(file)) {
                file.forEach((item) => {
                    this.pushFile(item);
                });
            } else {
                this.pushFile(file);
            }
        },
        pushFile(file) {
            this.form.attachments.push(file);
        },
        removeAttachment(index) {
            this.form.attachments.splice(index, 1);
        },
        closeModal() {
            this.$emit('closeModal');
        },
    },
    created() {
        this.queryTypes = queryTypes;
    },
};
</script>

<style scoped>

.o-support-contact-form {
    &__label {
        @apply
            block
            font-bold
            mb-2
            text-sm
        ;
    }
}

</style>
