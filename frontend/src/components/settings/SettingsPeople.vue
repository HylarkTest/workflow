<template>
    <div class="o-settings-people">
        <template
            v-if="isUserVerified"
        >
            <div
                class="bg-primary-100 rounded-xl p-4 mb-12"
            >
                <h2
                    class="font-semibold mb-2"
                >
                    Invite people to "{{ baseName }}"
                </h2>

                <FormWrapper
                    :form="form"
                    @submit="invitePeople"
                >

                    <div class="flex items-end gap-4 flex-wrap">
                        <div class="flex-1 min-w-[100px]">
                            <label
                                class="block label-data--subtle"
                            >
                                Email addresses (separate multiple emails with a comma)
                            </label>
                            <InputBox
                                v-model="emailsFormatted"
                                :error="errorMessage"
                                name="emails"
                                formField="emails"
                                placeholder="One or more emails"
                                @focusState="actionOnFocusState"
                            >
                            </InputBox>
                        </div>
                        <div>
                            <label
                                class="block label-data--subtle"
                            >
                                Role
                            </label>
                            <RoleSelect
                                v-model="form.role"
                                size="lg"
                                :roleArrName="roleArrName"
                            >
                            </RoleSelect>
                        </div>
                        <button
                            class="button button-primary"
                            :class="{ unclickable: inviteDisabled }"
                            :disabled="inviteDisabled"
                            type="submit"
                        >
                            Invite
                        </button>
                    </div>
                </FormWrapper>

            </div>

            <div>
                <h2
                    class="text-xl font-bold mb-5"
                >
                    Members
                </h2>

                <div>
                    <PeopleMember
                        v-for="member in members"
                        :key="member.id"
                        class="border-b border-solid border-gray-200 py-4 last:border-none"
                        :member="member"
                        :base="activeBase"
                        :isBaseOwner="isBaseOwner"
                        :hasOnlyOneOwner="hasOnlyOneOwner"
                        :isBaseOwnerOrAdmin="isBaseOwnerOrAdmin"
                    >
                    </PeopleMember>
                </div>
            </div>
        </template>

        <div
            v-else
            class="text-center"
        >
            <p
                class="mb-6 font-semibold"
            >
                Please verify your account to invite other people to this base
            </p>

            <div>
                <p class="mb-4 text-smbase">
                    Missed the verification email? Use the button below to get that resent!
                </p>
                <a
                    class="button-primary button"
                    href="/email/verification-notification"
                >
                    Resend activation email
                </a>
            </div>
        </div>
    </div>
</template>

<script>

import { gql } from '@apollo/client';

import MEMBER_FRAGMENT from '@/graphql/MemberFragment.gql';
import { checkIsEmailValid } from '@/core/validation.js';
import { switchingBases, inviteMember } from '@/core/repositories/baseRepository.js';

import RoleSelect from '@/components/settings/RoleSelect.vue';
import PeopleMember from '@/components/settings/PeopleMember.vue';

const memberQueries = ['memberAccepted', 'memberUpdated', 'memberDeleted'].map((event) => {
    return {
        client: 'defaultClient',
        query: gql`subscription ${event} {
            ${event} {
                node {
                    id
                    members { ...MemberFragment }
                }
                id
                role
            }
        }
        ${MEMBER_FRAGMENT}`,
        result() {
            // If accepted then we should update the invites
            if (event === 'memberAccepted') {
                this.$apollo.queries.baseWithInvites.refetch();
            }
        },
    };
});

const inviteQueries = [
    'memberInvited',
    'memberInviteUpdated',
    'memberInviteDeleted',
].map((event) => ({
    query: gql`subscription MemberInvited {
        ${event} {
            id
            email
            role
            invitedAt
        }
    }`,
    result() {
        if (event !== 'memberInvitedUpdated') {
            this.$apollo.queries.baseWithInvites.refetch();
        }
    },
}));

export default {
    name: 'SettingsPeople',
    components: {
        PeopleMember,
        RoleSelect,
    },
    mixins: [
    ],
    props: {
        user: {
            type: Object,
            required: true,
        },
    },
    apollo: {
        baseWithInvites: {
            query: gql`query BaseWithInvites {
                base {
                    node {
                        id
                        invites(status: PENDING) {
                            id
                            email
                            role
                            invitedAt
                        }
                    }
                }
            }`,
            update: (data) => {
                return data.base.node;
            },
            skip: () => {
                return switchingBases();
            },
            client: 'defaultClient',
        },
        $subscribe: [
            ...inviteQueries,
            ...memberQueries,
        ],
    },
    data() {
        return {
            form: this.$form({
                emails: [],
                role: 'MEMBER',
            }),
            allowInvalidError: false,
            processingInvite: false,
        };
    },
    computed: {
        activeBase() {
            return this.user.activeBase();
        },
        baseName() {
            return this.activeBase.name;
        },
        invites() {
            return this.baseWithInvites?.invites || [];
        },
        members() {
            return this.activeBase.members.concat(this.invites);
        },
        emailsFormatted: {
            get() {
                return this.form.emails.join(', ');
            },
            set(val) {
                const split = val.split(',');
                this.form.emails = _(split).map((email) => {
                    return email.trim();
                }).compact().value();
            },
        },
        hasInvalidEmail() {
            return this.form.emails.some((email) => {
                return !checkIsEmailValid(email);
            });
        },
        emailsLength() {
            return this.form.emails.length;
        },
        inviteDisabled() {
            return !this.emailsLength || this.hasInvalidEmail || this.processingInvite;
        },
        errorMessage() {
            return this.allowInvalidError && this.hasInvalidEmail
                ? 'Make sure all email addresses are valid to send the invite!'
                : '';
        },
        isBaseOwnerOrAdmin() {
            return this.isBaseOwner || this.isBaseAdmin;
        },
        userBaseObj() {
            return this.activeBase.pivot;
        },
        userBaseRole() {
            return this.userBaseObj?.role;
        },
        isBaseOwner() {
            return this.userBaseRole === 'OWNER';
        },
        isBaseAdmin() {
            return this.userBaseRole === 'ADMIN';
        },
        baseOwners() {
            return this.members.filter((member) => {
                return member.role === 'OWNER';
            });
        },
        baseOwnersLength() {
            return this.baseOwners.length;
        },
        hasOnlyOneOwner() {
            return this.baseOwnersLength === 1;
        },
        roleArrName() {
            return this.isBaseOwner ? 'ALL' : 'BASIC';
        },
        isUserVerified() {
            return this.user.verified;
        },
    },
    methods: {
        async invitePeople() {
            this.processingInvite = true;
            try {
                await inviteMember(this.form);
                this.form.reset();
                this.$successFeedback({
                    customHeaderPath: 'feedback.members.invites.header',
                    customMessagePath: 'feedback.members.invites.message',
                });
            } finally {
                this.processingInvite = false;
            }
        },
        actionOnFocusState(state) {
            if (state) {
                this.allowInvalidError = false;
            } else if (this.emailsLength) {
                this.allowInvalidError = true;
            }
        },
    },
    created() {
    },
};
</script>

<style scoped>

/*.o-settings-people {

} */

</style>
