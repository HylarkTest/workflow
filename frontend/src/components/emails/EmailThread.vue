<template>
    <div
        class="o-email-thread min-w-0"
        :class="[processingClass, boxStyleClass]"
    >
        <div
            class="flex items-center py-1 px-2 bg-cm-100"
            :class="hasBack ? 'justify-between' : 'justify-end'"
        >
            <button
                v-if="hasBack"
                class="button--sm button-primary"
                type="button"
                @click="returnToList"
            >
                <i class="fa-regular fa-arrow-left mr-1">
                </i>
                {{ $t('common.back') }}
            </button>

            <div
                class="flex items-center"
            >
                <button
                    v-if="isDraft"
                    class="button--sm button-primary"
                    type="button"
                    @click="resumeDraft"
                >
                    Edit draft
                </button>

                <div
                    class="flex"
                >
                    <RoundedIcon
                        v-for="action in validActions"
                        :key="action.val"
                        class="mx-1"
                        :title="getActionName(action)"
                        colorClasses="hover:bg-primary-200 text-cm-700 hover:text-primary-600"
                        :icon="getActionIcon(action)"
                        @click="doAction(action)"
                    >
                    </RoundedIcon>
                </div>
            </div>
        </div>

        <EmailAssociations
            v-if="emailAssociationsPossible"
            class="p-4"
            :email="email"
        >
        </EmailAssociations>

        <div class="px-4 text-2xl font-semibold mt-2">
            {{ subject }}
        </div>

        <EmailFull
            class="flex-1 min-h-0 h-full"
            :email="email"
        >
        </EmailFull>

        <ConfirmModal
            v-if="isModalOpen"
            headerTextPath="common.areYouSure"
            @closeModal="closeModal"
            @cancelAction="closeModal"
            @proceedWithAction="deleteEmail"
        >
            <p class="mb-3">
                {{ deleteConfirmText }}
            </p>
        </ConfirmModal>
    </div>
</template>

<script>
import EmailFull from './EmailFull.vue';
import EmailAssociations from './EmailAssociations.vue';
import ConfirmModal from '@/components/assets/ConfirmModal.vue';
import RoundedIcon from '@/components/buttons/RoundedIcon.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';
import assistsWithEntityQueries from '@/vue-mixins/features/assistsWithEntityQueries.js';

import { deleteEmail, markEmailRead, toggleEmailFlag } from '@/core/repositories/emailRepository.js';

import ENTITIES_EXIST from '@/graphql/items/EntitiesExist.gql';
import Email from '@/core/models/Email.js';

// const linkOptions = [
//     {
//         val: 'LINK',
//     },
//     {
//         val: 'CREATE_TODO',
//     },
//     {
//         val: 'CREATE_EVENT',
//     },
//     {
//         val: 'CREATE_RECORD',
//     },
// ];

const actions = [
    // {
    //     val: 'PRIORITY',
    //     icon: 'fa-exclamation',
    //     action: 'setPriority',
    //     condition: {
    //         checkKey: 'account.provider',
    //         checkVal: 'MICROSOFT',
    //     },
    // },
    {
        val: 'FLAG',
        icon: (email) => (email.isFlagged ? 'text-peach-600 fa-solid fa-flag-swallowtail' : 'fa-flag-swallowtail'),
        action: 'setFlag',
        condition: {
            checkKey: 'account.provider',
            checkVal: 'MICROSOFT',
        },
    },
    {
        val: 'STARRED',
        icon: (email) => (email.isFlagged ? 'text-gold-500 fa-solid fa-star' : 'fa-star'),
        action: 'setStarred',
        condition: {
            checkKey: 'account.provider',
            checkVal: 'GOOGLE',
        },
    },
    {
        val: 'REPLY',
        action: 'replyToEmail',
        icon: 'fa-reply',
    },
    {
        val: 'REPLY_ALL',
        action: 'replyToAll',
        icon: 'fa-reply-all',
    },
    {
        val: 'FORWARD',
        action: 'forwardEmail',
        icon: 'fa-share',
    },
    // {
    //     val: 'MOVE',
    //     action: 'moveEmail',
    //     icon: 'fa-arrows-left-right-to-line',
    // },
    {
        val: 'DELETE',
        draft: true,
        action: 'confirmDelete',
        icon: 'fa-trash-alt',
    },
];

export default {
    name: 'EmailThread',
    components: {
        EmailFull,
        RoundedIcon,
        ConfirmModal,
        EmailAssociations,
    },
    mixins: [
        interactsWithModal,
        assistsWithEntityQueries,
    ],
    props: {
        email: {
            type: Email,
            required: true,
        },
        hasBack: Boolean,
        hasHeader: Boolean,
        emailBoxStyle: {
            type: String,
            default: 'plain',
            validator(value) {
                return ['plain', 'border'].includes(value);
            },
        },
    },
    emits: [
        'respondToEmail',
        'resetEmailInView',
        'returnToList',
    ],
    apollo: {
        hasEntities: {
            query: ENTITIES_EXIST,
            variables() {
                return this.getRequestVariables({ withFeatures: 'EMAILS' });
            },
            update: (data) => !!data.allItems.pageInfo.total,
            fetchPolicy: 'no-cache',
        },
    },
    data() {
        return {
            deleteProcessing: false,
        };
    },
    computed: {
        processingClass() {
            return { unclickable: this.deleteProcessing };
        },
        subject() {
            return this.email.subject;
        },
        validActions() {
            if (this.isDraft) {
                return _.filter(actions, 'draft');
            }
            return actions.filter((action) => {
                if (!action.condition) {
                    return true;
                }
                const condition = action.condition;
                const emailVal = _.get(this.email, condition.checkKey);
                const checkVal = condition.checkVal;
                return checkVal === emailVal;
            });
        },
        isDraft() {
            return this.email.isDraft;
        },
        // emails() {
        //     return this.thread.emails;
        // },
        deleteConfirmText() {
            return this.isDraft
                ? 'Deleting this draft will delete it from your integrated mailbox.'
                : 'Deleting this email will delete it from your integrated mailbox.';
        },
        emailAssociationsPossible() {
            return this.hasEntities && !this.isDraft;
        },
        boxStyleClass() {
            return `o-email-thread__style--${this.emailBoxStyle}`;
        },
        topClass() {
            if (!this.hasHeader) {
                return 'top-38';
            }
            return this.hasBack ? 'top-[12.75rem]' : 'top-40';
        },
    },
    methods: {
        doAction(actionObj) {
            const action = actionObj.action;
            this[action]();
        },
        confirmDelete() {
            this.openModal();
        },
        async deleteEmail() {
            this.closeModal();
            this.deleteProcessing = true;
            try {
                await deleteEmail(this.email.account.id, this.email.mailbox.id, this.email.id);
                this.$emit('resetEmailInView');
            } finally {
                this.deleteProcessing = false;
            }
        },
        moveEmail() {
            return false;
        },
        forwardEmail() {
            this.$emit('respondToEmail', { email: this.email, action: 'FORWARD' });
        },
        replyToAll() {
            this.$emit('respondToEmail', { email: this.email, action: 'REPLY_ALL' });
        },
        replyToEmail() {
            this.$emit('respondToEmail', { email: this.email, action: 'REPLY' });
        },
        resumeDraft() {
            this.$emit('respondToEmail', { email: this.email, action: 'DRAFT' });
        },
        setStarred() {
            return this.setFlag();
        },
        setFlag() {
            return toggleEmailFlag(this.email, !this.email.isFlagged);
        },
        // setPriority() {
        //     return false;
        // },
        getActionName(action) {
            const val = _.camelCase(action.val);
            return this.$t(`emails.actions.${val}`);
        },
        getActionIcon(action) {
            if (_.isFunction(action.icon)) {
                return action.icon(this.email);
            }
            return action.icon;
        },
        returnToList() {
            this.$emit('returnToList');
        },
    },
    created() {
        // this.linkOptions = linkOptions;
        markEmailRead(this.email);
    },
};
</script>

<style scoped>

.o-email-thread {
    @apply
        bg-cm-00
        flex
        flex-col
        pb-4
        rounded-xl
    ;

    &__style--border {
        @apply
            border
            border-gray-200
            border-solid
            px-2
            rounded-xl
        ;
    }

    &__option {
        @apply
            border
            border-primary-600
            border-solid
            mr-2
            text-primary-700
        ;

        &:hover {
            @apply
                bg-primary-100
            ;
        }
    }
}

</style>
